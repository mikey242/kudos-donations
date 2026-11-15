import React from 'react';
import {
	useEffect,
	useState,
	createInterpolateElement,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { Button, ExternalLink } from '@wordpress/components';
import { useSettingsContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { AllSettings } from '../../types/all-settings';
import { Campaign } from '../../types/entity';
import { StepsBanner, type StepsBannerStep } from './StepsBanner';
import { getPaymentVendors } from '../../utils/payment-vendor';

// Matches PaymentProviderInterface::STEP_GO_LIVE.
const GO_LIVE_STEP = 'golive';

/**
 * The ID of the most recent campaign, null when there is none, or undefined while loading.
 *
 * @param params  The admin query params; a change triggers a refetch.
 * @param enabled Whether to fetch at all.
 */
function useFirstCampaignId(
	params: object,
	enabled: boolean
): number | null | undefined {
	const [campaignId, setCampaignId] = useState<number | null | undefined>(
		undefined
	);
	useEffect(() => {
		if (!enabled) {
			return;
		}
		apiFetch<Campaign[]>({
			path: 'kudos/v1/campaign?per_page=1',
		})
			.then((res) => setCampaignId(res[0]?.id ?? null))
			.catch(() => setCampaignId(null));
	}, [enabled, params]);
	return campaignId;
}

export const OnboardingBanner = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
	const { params, setParams } = useAdminQueryParams();
	const dismissed = !!settings._kudos_onboarding_dismissed;
	const campaignId = useFirstCampaignId(params, !dismissed);
	const placedUrl = settings._kudos_form_placed_url ?? '';

	const vendor = settings._kudos_payment_vendor ?? '';
	const status = settings._kudos_payment_vendor_status;

	if (dismissed || campaignId === undefined) {
		return null;
	}

	const paymentVendors = getPaymentVendors();
	const dismiss = () => updateSetting('_kudos_onboarding_dismissed', true);

	// The active provider declares its own setup steps; we only supply the ones that are
	// not vendor-specific. A provider with nothing to configure returns none. Going live
	// comes after the campaign, so a campaign can be built and tried in test mode first.
	const vendorSteps = (status?.steps ?? []).map((step) => ({
		id: step.id,
		// Until a provider is chosen, the steps come from the default one: don't name it,
		// and don't count its leftover keys as progress.
		label:
			step.id === 'connect' && !vendor
				? __('Connect your account', 'kudos-donations')
				: step.label,
		done: !!vendor && step.done,
		onClick: () =>
			setParams({
				page: 'kudos-settings',
				tab: 'payment',
				panel: step.panel,
			}),
	}));
	const setupSteps = vendorSteps.filter((step) => step.id !== GO_LIVE_STEP);
	const liveSteps = vendorSteps.filter((step) => step.id === GO_LIVE_STEP);

	const steps: StepsBannerStep[] = [
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
		...setupSteps,
		{
			// A campaign is created on activation, so the step that matters is getting a
			// form onto the site. Done once one has rendered on a public page.
			id: 'placed',
			label: __('Add to your site', 'kudos-donations'),
			done: !!placedUrl,
			onClick: () =>
				setParams(
					campaignId
						? { page: 'kudos-campaigns', entity: campaignId }
						: { page: 'kudos-campaigns' }
				),
		},
		...liveSteps,
	];

	// Once only going live is left, nudge towards a test donation before switching.
	const onlyLiveLeft =
		liveSteps.some((step) => !step.done) &&
		steps.every(
			(step) => step.done || step.hidden || step.id === GO_LIVE_STEP
		);

	return (
		<div className="admin-wrap" style={{ margin: '2em auto' }}>
			<StepsBanner
				title={
					onlyLiveLeft
						? __(
								'Try a test donation, then go live when you are ready',
								'kudos-donations'
							)
						: __(
								'Complete the following steps to get started',
								'kudos-donations'
							)
				}
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
									/* translators: 1: link to the page the form is on, 2: support forums link, 3: documentation link */
									'Your donation form is live on <page/>. Feel free to customise your campaign, and find out more by visiting our <forums/> or <docs/>.',
									'kudos-donations'
								),
								{
									page: (
										<ExternalLink href={placedUrl}>
											{placedUrl}
										</ExternalLink>
									),
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
