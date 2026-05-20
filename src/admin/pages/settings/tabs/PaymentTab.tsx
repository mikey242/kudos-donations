import { __ } from '@wordpress/i18n';
import type { AdminTab } from '../../AdminTabPanel';
import React from 'react';
import { useState } from '@wordpress/element';
import { useSettingsContext } from '../../../contexts';
import { Panel, ProviderSelector } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';
import { MolliePanels } from './MollieTab';
import { StripePanels } from './StripeTab';
import { Flex, Notice, Button } from '@wordpress/components';

const vendorComponents: Record<string, () => React.ReactNode> = {
	mollie: MolliePanels,
	stripe: StripePanels,
	demo: () => null,
};

const VendorSelectorPanel = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
	const vendors = window.kudos?.admin?.payment_vendors ?? [];
	const [modalOpen, setModalOpen] = useState(false);
	const [isSaving, setIsSaving] = useState(false);
	const savedVendor = settings._kudos_payment_vendor;

	const handleSave = async (slug: string) => {
		setIsSaving(true);
		try {
			await updateSetting(
				'_kudos_payment_vendor',
				slug as AllSettings['_kudos_payment_vendor']
			);
		} finally {
			setIsSaving(false);
			setModalOpen(false);
		}
	};

	const handleClose = () => {
		setModalOpen(false);
	};

	if (vendors.length <= 1) {
		return null;
	}

	const currentVendor = vendors.find((v) => v.slug === savedVendor);

	return (
		<>
			<Panel header={__('Payment Provider', 'kudos-donations')}>
				<Flex justify="space-between" align="center">
					<div
						style={{
							display: 'flex',
							alignItems: 'center',
							gap: '0.5em',
						}}
					>
						{currentVendor?.icon && (
							<img
								width={35}
								height={35}
								alt=""
								src={`data:image/svg+xml;utf8,${encodeURIComponent(currentVendor.icon)}`}
							/>
						)}
						<strong>{currentVendor?.label ?? savedVendor}</strong>
					</div>
					<Button
						variant="link"
						isDestructive
						onClick={() => setModalOpen(true)}
					>
						{__('Change provider', 'kudos-donations')}
					</Button>
				</Flex>
			</Panel>
			<ProviderSelector
				isOpen={modalOpen}
				onClose={handleClose}
				vendors={vendors}
				currentVendor={savedVendor}
				onSave={handleSave}
				isSaving={isSaving}
			>
				<Notice status="warning" isDismissible={false}>
					{__(
						'Switching payment providers will not migrate existing subscriptions or donor records.',
						'kudos-donations'
					)}
				</Notice>
			</ProviderSelector>
		</>
	);
};

const VendorPanels = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const vendors = window.kudos?.admin?.payment_vendors ?? [];
	const savedVendor = settings._kudos_payment_vendor;
	const isValid = vendors.some(({ slug }) => slug === savedVendor);
	const resolvedVendor = isValid ? savedVendor : vendors[0]?.slug;
	const Panels =
		(resolvedVendor && vendorComponents[resolvedVendor]) || MolliePanels;
	return <Panels />;
};

export const PaymentTab: AdminTab = {
	name: 'payment',
	title: __('Payment', 'kudos-donations'),
	panels: [
		{ name: 'vendor-selector', content: <VendorSelectorPanel /> },
		{ name: 'vendor-panels', content: <VendorPanels /> },
	],
};
