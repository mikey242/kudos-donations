/* eslint-disable camelcase */
import React from 'react';
import {
	createContext,
	useCallback,
	useContext,
	useMemo,
} from '@wordpress/element';

import { useEntityRecords } from '@wordpress/core-data';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Icon } from '@wordpress/components';
import type { Post } from '../../types/posts';
import { useAdminQueryParams } from '../hooks';

interface PostsContextValue<T extends Post = Post> {
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
}

const PostsContext = createContext<PostsContextValue<any> | null>(null);

interface PostsProviderProps {
	children: React.ReactNode;
	postType: string;
	singularName: string;
	pluralName: string;
}

export const PostsProvider = <T extends Post>({
	postType,
	singularName,
	pluralName,
	children,
}: PostsProviderProps) => {
	const { params } = useAdminQueryParams();
	const {
		paged,
		order,
		orderby,
		meta_key,
		meta_value,
		meta_query,
		metaType,
	} = params;
	const { saveEntityRecord, deleteEntityRecord } = useDispatch('core');
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const query = {
		per_page: 20,
		page: Number(paged),
		order,
		orderby,
		...(meta_key ? { meta_key } : {}),
		...(meta_value ? { meta_value } : {}),
		...(meta_query ? { meta_query } : {}),
		...(metaType ? { metaType } : {}),
	};
	const {
		records: posts,
		hasResolved,
		totalPages,
		totalItems,
	} = useEntityRecords<T>('postType', postType, query);

	const handleSave = useCallback(
		async (args = {}): Promise<T | null> => {
			try {
				const response = await saveEntityRecord(
					'postType',
					postType,
					args
				);

				if (!response) {
					throw new Error('No response from API.');
				}
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
		[createErrorNotice, postType, saveEntityRecord, singularName]
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
		async (args?: Partial<T> | React.SyntheticEvent): Promise<any> => {
			// If args is a SyntheticEvent, ignore it and create an empty args object
			if (
				args &&
				typeof (args as React.SyntheticEvent).preventDefault ===
					'function'
			) {
				args = {};
			}

			// Set default arguments.
			const {
				title = sprintf(
					/* translators: %s is the post type name. */
					__('New %s', 'kudos-donations'),
					singularName
				),
				status = 'publish',
				...rest
			} = (args as Partial<T>) || {};

			const response = await handleSave({
				title,
				status,
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
		async (postId: number): Promise<void> => {
			try {
				await deleteEntityRecord('postType', postType, postId, {
					force: true,
				});
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
			deleteEntityRecord,
			postType,
			createSuccessNotice,
			singularName,
			createErrorNotice,
		]
	);

	// Prepares data for duplicating current post.
	const handleDuplicate = useCallback(
		(post: T) => {
			const { id, ...rest } = post;

			const data = {
				...rest,
				title: { raw: post.title.raw, rendered: '' },
				date: new Date().toISOString(),
			} as Partial<T>;
			return handleSave(data);
		},
		[handleSave]
	);

	const data: PostsContextValue<T> = useMemo(
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
			totalItems,
			totalPages,
		]
	);

	return (
		<>
			<PostsContext.Provider value={data}>
				{children}
			</PostsContext.Provider>
		</>
	);
};

export const usePostsContext = <T extends Post>(): PostsContextValue<T> => {
	const context = useContext(PostsContext);
	if (!context) {
		throw new Error('usePostsContext must be used within a PostsProvider');
	}
	return context;
};
