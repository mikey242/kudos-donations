import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
import { useSettingsContext } from '../contexts';
import { queryEntities } from '../utils';
import type { AllSettings } from '../../types/all-settings';

const REVIEW_URL =
	'https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post';

/**
 * Whether the site has received at least one live (not test) donation.
 *
 * @param enabled Whether to check; skips the request when the prompt can't show anyway.
 */
function useHasLiveDonation(enabled: boolean): boolean {
	const [hasLiveDonation, setHasLiveDonation] = useState(false);

	useEffect(() => {
		if (!enabled) {
			return;
		}
		queryEntities('transaction', {
			per_page: 1,
			where: { status: 'paid', mode: 'live' },
		})
			.then((response) => setHasLiveDonation(response.total > 0))
			.catch(() => setHasLiveDonation(false));
	}, [enabled]);

	return hasLiveDonation;
}

/**
 * Asks once for a review on WordPress.org, after the site's first live donation, when the
 * plugin has visibly worked. Shown only on Kudos pages and once onboarding is out of the
 * way. Either answer hides it for good.
 */
export const ReviewRequest = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();
	const eligible =
		!!settings._kudos_onboarding_dismissed &&
		!settings._kudos_review_request_dismissed;
	const hasLiveDonation = useHasLiveDonation(eligible);

	if (!eligible || !hasLiveDonation) {
		return null;
	}

	const dismiss = () =>
		updateSetting('_kudos_review_request_dismissed', true);

	return (
		<div className="admin-wrap" style={{ margin: '2em auto' }}>
			<Notice
				status="info"
				onRemove={dismiss}
				actions={[
					{
						label: __('Leave a review', 'kudos-donations'),
						variant: 'primary',
						onClick: () => {
							window.open(REVIEW_URL, '_blank', 'noopener');
							void dismiss();
						},
					},
					{
						label: __('No thanks', 'kudos-donations'),
						variant: 'link',
						onClick: () => void dismiss(),
					},
				]}
			>
				<p>
					<strong>
						{__(
							'Great to see donations coming in!',
							'kudos-donations'
						)}
					</strong>{' '}
					{__(
						'If Kudos Donations helps your cause, would you leave a short review on WordPress.org? It helps other organisations find it.',
						'kudos-donations'
					)}
				</p>
			</Notice>
		</div>
	);
};
