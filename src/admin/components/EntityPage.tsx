import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import { usePostsContext } from '../contexts';
import type { Post } from '../../types/posts';
import { useAdminQueryParams } from '../hooks';

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
	const { params, updateParams } = useAdminQueryParams();
	const { post: postId } = params;
	const [currentPost, setCurrentPost] = useState<Post | null>(null);
	const { posts, handleNew } = usePostsContext<Post>();

	const newPost = async (input: React.SyntheticEvent | Partial<Post>) => {
		await handleNew(input).then((response) => {
			if (response?.id) {
				updateParams({ post: response.id });
			}
		});
	};

	const editPost = (id: number) => {
		void updateParams({ post: id });
	};

	useEffect(() => {
		if (postId && posts) {
			const found = posts.find((post) => post.id === Number(postId));
			setCurrentPost(found ?? null);
		}
	}, [postId, posts]);

	return (
		<>
			{postId && renderEdit ? (
				<div className="admin-wrap"> {renderEdit(currentPost)}</div>
			) : (
				<div className="admin-wrap-wide">
					{renderTable(editPost, newPost)}
				</div>
			)}
		</>
	);
};
