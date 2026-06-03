import { __ } from '@wordpress/i18n';
import type { AdminTab, AdminPanel } from '../../AdminTabPanel';
import React from 'react';
import { useState } from '@wordpress/element';
import { useSettingsContext } from '../../../contexts';
import { Panel } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';
import { molliePanels } from './MolliePanels';
import { stripePanels } from './StripePanels';
import { Flex, Notice } from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch } from '@wordpress/data';
import { ProviderSelector } from '../../../controls';

const vendorPanels: Record<string, AdminPanel[]> = {
	mollie: molliePanels,
	stripe: stripePanels,
	demo: [],
};

const VendorSelectorPanel = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
	const vendors = window.kudos?.admin?.payment_vendors ?? [];
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

	if (vendors.length <= 1) {
		return null;
	}

	return (
		<>
			<Panel header={__('Payment Provider', 'kudos-donations')}>
				<Flex justify="space-between" align="center">
					<ProviderSelector
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
				</Flex>
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
	const vendors = window.kudos?.admin?.payment_vendors ?? [];
	const savedVendor = settings._kudos_payment_vendor;
	const isValid = vendors.some(({ slug }) => slug === savedVendor);
	const resolvedVendor = isValid ? savedVendor : vendors[0]?.slug;
	const panels =
		(resolvedVendor && vendorPanels[resolvedVendor]) ?? molliePanels;

	return {
		name: 'payment',
		title: __('Payment', 'kudos-donations'),
		panels: [vendorSelectorPanel, ...panels],
	};
};
