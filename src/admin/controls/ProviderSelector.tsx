import { useState } from '@wordpress/element';
import { Button, Modal, RadioControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { AllSettings } from '../../types/all-settings';

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
			<div
				style={{
					display: 'flex',
					alignItems: 'center',
					gap: '0.5em',
				}}
			>
				{selectedVendor?.icon && (
					<img
						width={35}
						height={35}
						alt=""
						src={`data:image/svg+xml;utf8,${encodeURIComponent(selectedVendor.icon)}`}
					/>
				)}
				<strong style={{ marginRight: '0.5em' }}>
					{selectedVendor?.label ?? currentVendor}
				</strong>
			</div>
			<Button isDestructive onClick={() => setModalOpen(true)}>
				{__('Switch', 'kudos-donations-plus')}
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
		<div
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
						<img
							style={{ opacity: '0.6' }}
							key={vendor.slug}
							width={size}
							height={size}
							alt=""
							src={`data:image/svg+xml;utf8,${encodeURIComponent(vendor.icon)}`}
						/>
					);
				})}
		</div>
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
