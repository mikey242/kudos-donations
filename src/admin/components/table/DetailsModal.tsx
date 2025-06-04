import type { Post } from '../../../types/posts';
import { useState } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { PostMeta } from '../DefaultEditView';
import React from 'react';

export const DetailsModal = ({ post }: { post: Post }) => {
	const [isOpen, setIsOpen] = useState(false);

	return (
		<>
			<Button
				icon="info"
				size="small"
				label={__('View donor details', 'kudos-donations')}
				onClick={() => setIsOpen((prev) => !prev)}
				isPressed={isOpen}
			/>
			{isOpen && (
				<Modal onRequestClose={() => setIsOpen(false)}>
					<PostMeta post={post} />
				</Modal>
			)}
		</>
	);
};
