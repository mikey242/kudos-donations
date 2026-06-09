import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody, Icon } from '@wordpress/components';
import { EntityRestResponse, useSettingsContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { AllSettings } from '../../types/all-settings';
import { Campaign } from '../../types/entity';

interface OnboardingStep {
	label: string;
	done: boolean;
	disabled?: boolean;
	onNavigate: () => void;
}

export const OnboardingBanner = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const { params, setParams } = useAdminQueryParams();
	const [hasCampaign, setHasCampaign] = useState<boolean | null>(null);

	const vendors = window.kudos?.admin?.payment_vendors ?? [];
	const vendor = settings._kudos_payment_vendor ?? '';
	const vendorReady = settings._kudos_payment_vendor_status?.ready === true;
	const apiMode = (settings as any)[
		`_kudos_vendor_${vendor}_api_mode`
	] as string;

	useEffect(() => {
		apiFetch<EntityRestResponse<Campaign>[]>({
			path: 'kudos/v1/campaign?per_page=1',
		})
			.then((res) => setHasCampaign(res.length > 0))
			.catch(() => setHasCampaign(false));
	}, [params]);

	if (hasCampaign === null) {
		return null;
	}

	const allSteps: OnboardingStep[] = [
		{
			label: __('Choose a payment provider', 'kudos-donations'),
			disabled: vendors.length <= 1,
			done: !!vendor,
			onNavigate: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'vendor-selector',
				}),
		},
		{
			label: __('Enter API keys', 'kudos-donations'),
			done: vendorReady,
			onNavigate: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'apikeys',
				}),
		},
		{
			label: __('Switch to live mode', 'kudos-donations'),
			done: vendorReady && apiMode === 'live',
			onNavigate: () =>
				setParams({
					page: 'kudos-settings',
					tab: 'payment',
					panel: 'apimode',
				}),
		},
		{
			label: __('Create a campaign', 'kudos-donations'),
			done: hasCampaign,
			onNavigate: () => setParams({ page: 'kudos-campaigns' }),
		},
	];

	const steps = allSteps.filter((s) => !s.disabled);

	if (steps.every((s) => s.done)) {
		return null;
	}

	const doneCount = steps.filter((s) => s.done).length;

	return (
		<div className="admin-wrap">
			<Card className="kudos-onboarding-banner" size="large">
				<div
					style={{
						height: '4px',
						background: '#e0e0e0',
						borderRadius: '2px 2px 0 0',
					}}
				>
					<div
						style={{
							height: '100%',
							width: `${(doneCount / steps.length) * 100}%`,
							background: 'var(--kudos-colour-success)',
							borderRadius: '2px 2px 0 0',
							transition: 'width 0.3s ease',
						}}
					/>
				</div>
				<CardBody>
					<h2 style={{ textAlign: 'center', marginTop: 0 }}>
						Complete the following steps to get started
					</h2>
					<div
						style={{
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'space-between',
						}}
					>
						<strong style={{ whiteSpace: 'nowrap' }}>
							{__('Setup', 'kudos-donations')} ({doneCount}/
							{steps.length})
						</strong>
						{steps.map((step) => (
							<Button
								style={{
									background: step.done
										? 'rgba(53, 172, 53, 0.1)'
										: 'rgba(46, 196, 182, 0.1)',
									borderRadius: '20px',
								}}
								key={step.label}
								onClick={step.onNavigate}
								disabled={step.done}
								icon={
									<Icon
										icon={step.done ? 'yes-alt' : 'marker'}
										style={{
											color: step.done
												? 'var(--kudos-colour-success)'
												: '#bbb',
											flexShrink: 0,
										}}
									/>
								}
							>
								{step.label}
							</Button>
						))}
					</div>
				</CardBody>
			</Card>
		</div>
	);
};
