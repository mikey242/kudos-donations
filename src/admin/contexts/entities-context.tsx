/* eslint-disable camelcase */
import React from 'react';
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';

import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Icon } from '@wordpress/components';
import type { BaseEntity } from '../../types/posts';
import { useAdminQueryParams } from '../hooks';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

interface EntitiesContextValue<T extends BaseEntity = BaseEntity> {
	posts: T[];
	hasResolved: boolean;
	totalPages: number;
	totalItems: number;
	handleNew: (args?: Partial<T> | React.SyntheticEvent) => Promise<any>;
	handleUpdate: (data: Partial<T>) => Promise<any>;
	handleDelete: (postId: number) => void;
	handleDuplicate: (post: T) => void;
	singularName: string;
	pluralName: string;
	postType: string;
}

export interface EntityRestResponse<T extends BaseEntity> {
	items: T[];
	total: number;
	total_pages: number;
	per_page: number;
	paged: number;
}

const EntitiesContext = createContext<EntitiesContextValue<any> | null>(null);

interface EntitiesProviderProps {
	children: React.ReactNode;
	postType: string;
	singularName: string;
	pluralName: string;
}

export const EntitiesProvider = <T extends BaseEntity>({
	postType,
	singularName,
	pluralName,
	children,
}: EntitiesProviderProps) => {
	const { params } = useAdminQueryParams();
	const { paged, order, orderby, column, value } = params;
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);

	const [posts, setPosts] = useState<T[]>([]);
	const [hasResolved, setHasResolved] = useState(false);
	const [totalPages, setTotalPages] = useState(1);
	const [totalItems, setTotalItems] = useState(0);

	const fetchPosts = useCallback(async () => {
		try {
			const args = { paged, orderby, order, column, value };
			const response: EntityRestResponse<T> = await apiFetch({
				path: addQueryArgs(`/kudos/v1/${postType}`, args),
			});
			setPosts(response.items);
			setHasResolved(true);
			setTotalItems(response.total);
			setTotalPages(response.total_pages);
		} catch (error) {
			setHasResolved(true);
		}
	}, [postType, paged, order, orderby, column, value]);

	useEffect(() => {
		void fetchPosts();
	}, [fetchPosts]);

	const handleSave = useCallback(
		async (args = {}): Promise<T | null> => {
			try {
				const response = await apiFetch<T>({
					path: `/kudos/v1/${postType}`,
					method: 'POST',
					data: args,
				});

				await fetchPosts();

				return response;
			} catch (error: any) {
				void createErrorNotice(
					sprintf(
						/* translators: %1$s is the post type and %2$s is the error message. */
						__('Error creating %1$s: %2$s', 'kudos-donations'),
						singularName,
						error.message
					)
				);
				return null;
			}
		},
		[createErrorNotice, fetchPosts, postType, singularName]
	);

	const handleUpdate = useCallback(
		async (data: Partial<T>) => {
			const response = await handleSave(data);
			void createSuccessNotice(
				sprintf(
					/* translators: %s is the post type singular name. */
					__('%s updated', 'kudos-donations'),
					singularName
				),
				{
					type: 'snackbar',
					icon: <Icon icon="saved" />,
				}
			);
			return response;
		},
		[createSuccessNotice, handleSave, singularName]
	);

	// Handles creating a post.
	const handleNew = useCallback(
		async (args?: Partial<T>): Promise<any> => {
			// Set default arguments.
			const {
				title = sprintf(
					/* translators: %s is the post type name. */
					__('New %s', 'kudos-donations'),
					singularName
				),
				...rest
			} = (args as Partial<T>) || {};

			const response = await handleSave({
				title,
				...rest,
			});

			if (response) {
				void createSuccessNotice(
					sprintf(
						/* translators: %s is the post type name. */
						__('%s created', 'kudos-donations'),
						singularName
					),
					{
						type: 'snackbar',
						icon: <Icon icon="plus" />,
					}
				);
			}
			return response;
		},
		[createSuccessNotice, handleSave, singularName]
	);

	const handleDelete = useCallback(
		async (id: number): Promise<void> => {
			try {
				await apiFetch({
					path: `/kudos/v1/${postType}/${id}`,
					method: 'DELETE',
				});
				await fetchPosts();
				await createSuccessNotice(
					sprintf(
						/* translators: %s is the post type name. */
						__('%s deleted', 'kudos-donations'),
						singularName
					),
					{
						type: 'snackbar',
						icon: <Icon icon="trash" />,
					}
				);
			} catch (error: any) {
				await createErrorNotice(
					sprintf(
						/* translators: %1$s is the post type and %2$s is the error message. */
						__('Error deleting %1$s: %2$s', 'kudos-donations'),
						singularName,
						error?.message ?? 'Unknown error'
					),
					{ type: 'snackbar' }
				);
			}
		},
		[
			postType,
			fetchPosts,
			createSuccessNotice,
			singularName,
			createErrorNotice,
		]
	);

	// Prepares data for duplicating current post.
	const handleDuplicate = useCallback(
		(post: T) => {
			const { id, wp_post_id, ...rest } = post;

			const data = {
				...rest,
				title: post.title,
				created_at: new Date().toISOString(),
			} as Partial<T>;
			return handleSave(data);
		},
		[handleSave]
	);

	const data: EntitiesContextValue<T> = useMemo(
		() => ({
			posts,
			totalItems,
			totalPages,
			hasResolved,
			handleNew,
			handleDuplicate,
			handleDelete,
			handleUpdate,
			singularName,
			pluralName,
			postType,
		}),
		[
			posts,
			handleDelete,
			handleDuplicate,
			handleNew,
			handleUpdate,
			hasResolved,
			singularName,
			pluralName,
			postType,
			totalItems,
			totalPages,
		]
	);

	return (
		<>
			<EntitiesContext.Provider value={data}>
				{children}
			</EntitiesContext.Provider>
		</>
	);
};

export const useEntitiesContext = <
	T extends BaseEntity,
>(): EntitiesContextValue<T> => {
	const context = useContext(EntitiesContext);
	if (!context) {
		throw new Error(
			'useEntitiesContext must be used within a EntitiesProvider'
		);
	}
	return context;
};
