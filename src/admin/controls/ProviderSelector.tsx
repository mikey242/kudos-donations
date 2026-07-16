import { useState } from '@wordpress/element';
import { Button, Modal, RadioControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { AllSettings } from '../../types/all-settings';
import { PaymentVendorIcon } from '../components';

export type Provider = {
	slug: string;
	label: string;
	icon?: string;
};

type ProviderSelectorProps = {
	vendors: Provider[];
	currentVendor: string;
	isSaving?: boolean;
	children?: React.ReactNode;
	onSave: (slug: string) => Promise<AllSettings>;
};

type ProviderSelectorModalProps = {
	onClose: () => void;
	vendors: Provider[];
	selectedVendor: string;
	onSave: (slug: string) => void;
	isSaving?: boolean;
	children?: React.ReactNode;
};

export const ProviderSelector = ({
	vendors,
	currentVendor,
	isSaving,
	children,
	onSave,
}: ProviderSelectorProps) => {
	const selectedVendor = vendors.find((v) => v.slug === currentVendor);
	const [modalOpen, setModalOpen] = useState(false);

	const saveAndClose = (slug: string) => {
		onSave(slug).then(() => setModalOpen(false));
	};

	return (
		<>
			{selectedVendor && (
				<div
					style={{
						display: 'flex',
						alignItems: 'center',
						gap: '0.5em',
					}}
				>
					<PaymentVendorIcon icon={selectedVendor.icon} size={35} />
					<strong style={{ marginRight: '0.5em' }}>
						{selectedVendor.label}
					</strong>
				</div>
			)}
			<Button
				variant={selectedVendor ? 'tertiary' : 'secondary'}
				isDestructive={!!selectedVendor}
				onClick={() => setModalOpen(true)}
			>
				{selectedVendor
					? __('Switch', 'kudos-donations')
					: __('Choose a payment provider', 'kudos-donations')}
				<OtherVendors
					vendors={vendors}
					currentSlug={selectedVendor?.slug ?? ''}
					style={{ marginLeft: '10px' }}
				/>
			</Button>
			{modalOpen && (
				<ProviderSelectorModal
					onClose={() => setModalOpen(false)}
					vendors={vendors}
					selectedVendor={selectedVendor?.slug ?? ''}
					onSave={saveAndClose}
					isSaving={isSaving}
				>
					{children}
				</ProviderSelectorModal>
			)}
		</>
	);
};

const OtherVendors = ({
	vendors,
	currentSlug,
	style,
}: {
	vendors: Provider[];
	currentSlug: string;
	style?: React.CSSProperties;
}) => {
	const total = vendors.length;
	return (
		<span
			style={{
				display: 'flex',
				gap: '0.2em',
				alignItems: 'center',
				...style,
			}}
		>
			{vendors
				.filter((v) => v.slug !== currentSlug)
				.map((vendor, i) => {
					const size = total === 1 ? 20 : 20 - (10 * i) / (total - 1);
					return (
						<PaymentVendorIcon
							key={vendor.slug}
							icon={vendor.icon}
							size={size}
							style={{ opacity: '0.6' }}
						/>
					);
				})}
		</span>
	);
};

const ProviderSelectorModal = ({
	onClose,
	vendors,
	selectedVendor,
	onSave,
	isSaving = false,
	children,
}: ProviderSelectorModalProps) => {
	const [selected, setSelected] = useState(selectedVendor);

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
			<div
				style={{
					display: 'flex',
					justifyContent: 'flex-end',
				}}
			>
				<Button variant="tertiary" onClick={onClose}>
					{__('Cancel', 'kudos-donations')}
				</Button>
				<Button
					variant="primary"
					isBusy={isSaving}
					disabled={isSaving || selected === selectedVendor}
					onClick={() => onSave(selected)}
				>
					{__('Save', 'kudos-donations')}
				</Button>
			</div>
		</Modal>
	);
};
