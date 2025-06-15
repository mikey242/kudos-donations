import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import { usePostsContext } from '../contexts';
import type { BaseEntity } from '../../types/posts';
import { useAdminQueryParams } from '../hooks';

interface EntityPageProps {
	renderTable: (
		editPost: (id: string | number) => void,
		newPost: (e: React.SyntheticEvent | Partial<BaseEntity>) => void
	) => React.ReactNode;
	renderEdit?: (post: BaseEntity) => React.ReactNode;
}

export const EntityPage = ({
	renderTable,
	renderEdit,
}: EntityPageProps): React.ReactNode => {
	const { params, updateParams } = useAdminQueryParams();
	const { post: postId } = params;
	const [currentPost, setCurrentPost] = useState<BaseEntity | null>(null);
	const { posts, handleNew } = usePostsContext<BaseEntity>();

	const newPost = async (
		input: React.SyntheticEvent | Partial<BaseEntity>
	) => {
		await handleNew().then((response) => {
			console.log(response);
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
			const found = posts.find(
				(post) => Number(post.id) === Number(postId)
			);
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
