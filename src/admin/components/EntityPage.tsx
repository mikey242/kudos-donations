import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import { usePostsContext } from './contexts';
import type { Post } from '../../types/posts';
import { parseAsInteger, useQueryState } from 'nuqs';

interface EntityPageProps {
	renderTable: (
		editPost: (id: string | number) => void,
		newPost: (e: React.SyntheticEvent | Partial<Post>) => void
	) => React.ReactNode;
	renderEdit?: (post: Post) => React.ReactNode;
}

export const EntityPage = ({
	renderTable,
	renderEdit,
}: EntityPageProps): React.ReactNode => {
	const [postId, setPostId] = useQueryState('edit', parseAsInteger);
	const [currentPost, setCurrentPost] = useState<Post | null>(null);
	const { posts, handleNew } = usePostsContext<Post>();

	const newPost = async (input: React.SyntheticEvent | Partial<Post>) => {
		await handleNew(input).then((response) => {
			if (response?.id) {
				setPostId(response.id);
			}
		});
	};

	const editPost = async (id: number) => {
		await setPostId(id);
	};

	useEffect(() => {
		if (postId && posts) {
			const found = posts.find((post) => post.id === Number(postId));
			setCurrentPost(found ?? null);
		}
	}, [postId, posts]);

	return (
		<>
			{postId && renderEdit
				? renderEdit(currentPost)
				: renderTable(editPost, newPost)}
		</>
	);
};
