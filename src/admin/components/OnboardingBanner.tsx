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
import { getPaymentVendors } from '../../utils/payment-vendor';

function useHasCampaign(params: object, enabled: boolean): boolean | null {
	const [hasCampaign, setHasCampaign] = useState<boolean | null>(null);
	useEffect(() => {
		if (!enabled) {
			return;
		}
		apiFetch<EntityRestResponse<Campaign>[]>({
			path: 'kudos/v1/campaign?per_page=1',
		})
			.then((res) => setHasCampaign(res.length > 0))
			.catch(() => setHasCampaign(false));
	}, [enabled, params]);
	return hasCampaign;
}

export const OnboardingBanner = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
	const { params, setParams } = useAdminQueryParams();
	const dismissed = !!settings._kudos_onboarding_dismissed;
	const hasCampaign = useHasCampaign(params, !dismissed);

	const vendor = settings._kudos_payment_vendor ?? '';
	const status = settings._kudos_payment_vendor_status;

	if (dismissed || hasCampaign === null) {
		return null;
	}

	const paymentVendors = getPaymentVendors();
	const dismiss = () => updateSetting('_kudos_onboarding_dismissed', true);

	// The active provider declares its own setup steps; we only supply the ones that are
	// not vendor-specific. A provider with nothing to configure returns none.
	const vendorSteps = (status?.steps ?? []).map(
		(step: { id: any; label: any; done: any; panel: any }) => ({
			id: step.id,
			label: step.label,
			done: step.done,
			onClick: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: step.panel,
				}),
		})
	);

	const steps = [
		{
			id: 'provider',
			label: __('Choose a payment provider', 'kudos-donations'),
			done: !!vendor,
			hidden: paymentVendors.length <= 1,
			onClick: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'vendor-selector',
				}),
		},
		...vendorSteps,
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
										<ExternalLink
											href="https://wordpress.org/support/plugin/kudos-donations/"
											rel="external noreferrer noopener"
										>
											{__(
												'support forums',
												'kudos-donations'
											)}
										</ExternalLink>
									),
									docs: (
										<ExternalLink
											href="https://docs.kudosdonations.com/"
											rel="external noreferrer noopener"
										>
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
