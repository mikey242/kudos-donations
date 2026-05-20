import { useEffect, useState } from '@wordpress/element';
import { Button, Flex, Modal, RadioControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';

export type Provider = {
	slug: string;
	label: string;
	icon?: string;
};

type ProviderSelectorProps = {
	isOpen: boolean;
	onClose: () => void;
	vendors: Provider[];
	currentVendor: string;
	onSave: (slug: string) => void;
	isSaving?: boolean;
	children?: React.ReactNode;
};

export const ProviderSelector = ({
	isOpen,
	onClose,
	vendors,
	currentVendor,
	onSave,
	isSaving = false,
	children,
}: ProviderSelectorProps) => {
	const [selected, setSelected] = useState(currentVendor);

	useEffect(() => {
		if (isOpen) {
			setSelected(currentVendor);
		}
	}, [isOpen, currentVendor]);

	if (!isOpen) {
		return null;
	}

	return (
		<Modal
			title={__('Change provider', 'kudos-donations')}
			onRequestClose={onClose}
		>
			{children}
			<RadioControl
				onChange={setSelected}
				selected={selected}
				options={vendors.map((vendor) => ({
					label: vendor.label,
					value: vendor.slug,
				}))}
			/>
			<Flex justify="flex-end">
				<Button variant="tertiary" onClick={onClose}>
					{__('Cancel', 'kudos-donations')}
				</Button>
				<Button
					variant="primary"
					isBusy={isSaving}
					disabled={isSaving || selected === currentVendor}
					onClick={() => onSave(selected)}
				>
					{__('Save', 'kudos-donations')}
				</Button>
			</Flex>
		</Modal>
	);
};
