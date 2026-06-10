import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { EntityRestResponse, useSettingsContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { AllSettings } from '../../types/all-settings';
import { Campaign } from '../../types/entity';
import { StepsBanner } from './StepsBanner';

function useHasCampaign(params: object): boolean | null {
	const [hasCampaign, setHasCampaign] = useState<boolean | null>(null);
	useEffect(() => {
		apiFetch<EntityRestResponse<Campaign>[]>({
			path: 'kudos/v1/campaign?per_page=1',
		})
			.then((res) => setHasCampaign(res.length > 0))
			.catch(() => setHasCampaign(false));
	}, [params]);
	return hasCampaign;
}

export const OnboardingBanner = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const { params, setParams } = useAdminQueryParams();
	const hasCampaign = useHasCampaign(params);

	const vendors = window.kudos?.admin?.payment_vendors ?? [];
	const vendor = settings._kudos_payment_vendor ?? '';
	const liveKey = settings[`_kudos_vendor_${vendor}_api_key_live`];
	const vendorReady = settings._kudos_payment_vendor_status?.ready === true;
	const vendorApiModes: Record<string, 'test' | 'live'> = {
		mollie: settings._kudos_vendor_mollie_api_mode,
		stripe: settings._kudos_vendor_stripe_api_mode,
	};
	const apiMode = vendorApiModes[vendor];

	if (hasCampaign === null) {
		return null;
	}

	const steps = [
		{
			id: 'provider',
			label: __('Choose a payment provider', 'kudos-donations'),
			done: !!vendor,
			hidden: vendors.length <= 1,
			onClick: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'vendor-selector',
				}),
		},
		{
			id: 'apikeys',
			label: __('Enter live API key', 'kudos-donations'),
			done: !!liveKey,
			onClick: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'apikeys',
				}),
		},
		{
			id: 'livemode',
			label: __('Switch to live mode', 'kudos-donations'),
			done: vendorReady && apiMode === 'live',
			onClick: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'apimode',
				}),
		},
		{
			id: 'campaign',
			label: __('Create a campaign', 'kudos-donations'),
			done: hasCampaign,
			onClick: () => setParams({ page: 'kudos-campaigns' }),
		},
	];

	return (
		<div className="admin-wrap" style={{ margin: '2em auto' }}>
			<StepsBanner
				title={__(
					'Complete the following steps to get started',
					'kudos-donations'
				)}
				counterLabel={__('Setup', 'kudos-donations')}
				steps={steps}
				className="kudos-onboarding-banner"
			/>
		</div>
	);
};
