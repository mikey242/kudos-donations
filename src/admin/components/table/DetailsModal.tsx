import { useState } from '@wordpress/element';
import type { IconType } from '@wordpress/components';
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';

interface DetailsModalProps {
	content: React.ReactNode | string;
	title?: string;
	modalSize?: 'small' | 'fill' | 'large' | 'medium';
	icon?: IconType;
}

export const DetailsModal = ({
	content,
	title,
	modalSize = 'small',
	icon = 'info-outline',
}: DetailsModalProps) => {
	const [isOpen, setIsOpen] = useState(false);
	return (
		<>
			<Button
				icon={icon}
				size="compact"
				label={__('View details', 'kudos-donations')}
				onClick={() => setIsOpen((prev) => !prev)}
				isPressed={isOpen}
			/>
			{isOpen && (
				<Modal
					title={title}
					size={modalSize}
					onRequestClose={() => setIsOpen(false)}
				>
					{content}
				</Modal>
			)}
		</>
	);
};
