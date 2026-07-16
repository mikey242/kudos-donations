import { __ } from '@wordpress/i18n';
import type { AdminTab, AdminPanel } from '../../AdminTabPanel';
import React from 'react';
import { useState } from '@wordpress/element';
import { useSettingsContext } from '../../../contexts';
import { Panel } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';
import { molliePanels } from './MolliePanels';
import { stripePanels } from './StripePanels';
import { Notice } from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch } from '@wordpress/data';
import { ProviderSelector } from '../../../controls';
import { getPaymentVendors } from '../../../../utils/payment-vendor';

const vendorPanels: Record<string, AdminPanel[]> = {
	mollie: molliePanels,
	stripe: stripePanels,
	demo: [],
};

const VendorSelectorPanel = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
	const paymentVendors = getPaymentVendors();
	const { createErrorNotice } = useDispatch(noticesStore);

	const [isSaving, setIsSaving] = useState(false);
	const savedVendor = settings._kudos_payment_vendor;

	const handleSave = async (slug: string) => {
		setIsSaving(true);
		try {
			return await updateSetting('_kudos_payment_vendor', slug);
		} catch (e) {
			await createErrorNotice((e as Error).message);
		} finally {
			setIsSaving(false);
		}
	};

	if (paymentVendors.length <= 1) {
		return null;
	}

	return (
		<>
			<Panel header={__('Payment Provider', 'kudos-donations')}>
				<div
					style={{ display: 'flex', justifyContent: 'space-between' }}
				>
					<ProviderSelector
						vendors={paymentVendors}
						currentVendor={savedVendor}
						onSave={handleSave}
						isSaving={isSaving}
					>
						<div style={{ paddingBottom: '10px' }}>
							<h3>
								{__(
									'Select the active payment provider for new transactions.',
									'kudos-donations'
								)}
							</h3>
							<Notice status="info" isDismissible={false}>
								{__(
									'Previously created transactions and subscriptions will remain with their original provider.',
									'kudos-donations'
								)}
							</Notice>
						</div>
					</ProviderSelector>
				</div>
			</Panel>
		</>
	);
};

const vendorSelectorPanel: AdminPanel = {
	name: 'vendor-selector',
	content: <VendorSelectorPanel />,
};

export const usePaymentTab = (): AdminTab => {
	const { settings } = useSettingsContext<AllSettings>();
	const paymentVendors = getPaymentVendors();
	const savedVendor = settings._kudos_payment_vendor;
	const isValid = paymentVendors.some(({ slug }) => slug === savedVendor);
	const resolvedVendor = isValid ? savedVendor : paymentVendors[0]?.slug;
	const panels =
		(resolvedVendor && vendorPanels[resolvedVendor]) ?? molliePanels;

	return {
		name: 'payment',
		title: __('Payment', 'kudos-donations'),
		panels: [vendorSelectorPanel, ...panels],
	};
};
