import React from 'react';
import {
	useEffect,
	useState,
	createInterpolateElement,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { Button, ExternalLink } from '@wordpress/components';
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
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
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

	if (hasCampaign === null || settings._kudos_onboarding_dismissed) {
		return null;
	}

	const dismiss = () => updateSetting('_kudos_onboarding_dismissed', true);

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
				onClose={dismiss}
				completedMessage={
					<div style={{ textAlign: 'center' }}>
						<h2 style={{ margin: 0 }}>
							{__("You're all set!", 'kudos-donations')}
						</h2>
						<p>
							{createInterpolateElement(
								__(
									/* translators: 1: support forums link, 2: documentation link */
									"Feel free to customise your campaign. Once you're ready place your campaign on your website using a shortcode or block. Find out more by visiting our <forums/> or <docs/>.",
									'kudos-donations'
								),
								{
									forums: (
										<ExternalLink href="https://wordpress.org/support/plugin/kudos-donations/">
											{__(
												'support forums',
												'kudos-donations'
											)}
										</ExternalLink>
									),
									docs: (
										<ExternalLink href="https://docs.kudosdonations.com/">
											{__(
												'documentation',
												'kudos-donations'
											)}
										</ExternalLink>
									),
								}
							)}
						</p>
						<Button variant="primary" onClick={dismiss}>
							{__('Dismiss', 'kudos-donations')}
						</Button>
					</div>
				}
			/>
		</div>
	);
};
