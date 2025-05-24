import React from 'react';
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
// eslint-disable-next-line import/default
import { useEntityRecords } from '@wordpress/core-data';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Icon } from '@wordpress/components';
import { useAdminContext } from './AdminContext';
import type { Post } from '../../../types/posts';

interface PostsContextValue<T extends Post = Post> {
	posts: T[];
	hasResolved: boolean;
	isLoading: boolean;
	hasLoadedOnce: boolean;
	totalPages: number;
	totalItems: number;
	handleNew: (args?: Partial<T> | React.SyntheticEvent) => Promise<any>;
	handleUpdate: (data: Partial<T>) => Promise<any>;
	handleDelete: (postId: number) => void;
	handleDuplicate: (post: T) => void;
}

const PostsContext = createContext<PostsContextValue<any> | null>(null);

interface PostsProviderProps {
	children: React.ReactNode;
	singular?: string;
	postType: string;
}

export const PostsProvider = <T extends Post>({
	postType,
	singular = 'Post',
	children,
}: PostsProviderProps) => {
	const { searchParams } = useAdminContext();
	const { saveEntityRecord, deleteEntityRecord } = useDispatch('core');
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const [cachedPosts, setCachedPosts] = useState<T[]>([]);
	const [isLoading, setIsLoading] = useState<boolean>(true);
	const [hasLoadedOnce, setHasLoadedOnce] = useState<boolean>(false);
	const {
		records: posts,
		hasResolved,
		totalPages,
		totalItems,
	} = useEntityRecords<T>('postType', postType, {
		per_page: 20,
		page: parseInt(searchParams.get('paged') ?? '1', 10),
		order: searchParams.get('order') ?? 'desc',
		orderby: searchParams.get('orderby') ?? 'date',
		metaKey: searchParams.get('meta_key') ?? '',
		metaValue: searchParams.get('meta_value') ?? '',
		metaCompare: searchParams.get('meta_compare') ?? '=',
		metaType: searchParams.get('meta_type') ?? 'string',
	});

	useEffect(() => {
		setIsLoading(!hasResolved);
		if (hasResolved) {
			setCachedPosts(posts ?? []);
			setHasLoadedOnce(true);
		}
	}, [posts, hasResolved]);

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
						singular,
						error.message
					)
				);
				return null;
			}
		},
		[createErrorNotice, postType, saveEntityRecord, singular]
	);

	const handleUpdate = useCallback(
		async (data: Partial<T>) => {
			const response = await handleSave(data);
			void createSuccessNotice(__('Post updated', 'kudos-donations'), {
				type: 'snackbar',
				icon: <Icon icon="saved" />,
			});
			return response;
		},
		[createSuccessNotice, handleSave]
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
				/* translators: %s is the post type name. */
				title = sprintf(__('New %s', 'kudos-donations'), singular),
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
					/* translators: %s is the post type name. */
					sprintf(__('%s created', 'kudos-donations'), singular),
					{
						type: 'snackbar',
						icon: <Icon icon="plus" />,
					}
				);
			}
			return response;
		},
		[createSuccessNotice, handleSave, singular]
	);

	const handleDelete = useCallback(
		async (postId: number): Promise<void> => {
			try {
				await deleteEntityRecord('postType', postType, postId, {
					force: true,
				});
				await createSuccessNotice(
					/* translators: %s is the post type name. */
					sprintf(__('%s deleted', 'kudos-donations'), singular),
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
						singular,
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
			singular,
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
			posts: cachedPosts,
			totalItems,
			totalPages,
			hasResolved,
			isLoading,
			hasLoadedOnce,
			handleNew,
			handleDuplicate,
			handleDelete,
			handleUpdate,
		}),
		[
			cachedPosts,
			handleDelete,
			handleDuplicate,
			handleNew,
			handleUpdate,
			hasLoadedOnce,
			hasResolved,
			isLoading,
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
