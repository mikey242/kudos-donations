import React from 'react';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
import { useSettingsContext } from '../contexts';
import type { AllSettings } from '../../types/all-settings';

const REVIEW_URL =
	'https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post';

/**
 * Asks once for a review on WordPress.org. Shown by AdminBanners once the site has a live
 * donation, when the plugin has visibly worked. Either answer hides it for good.
 */
export const ReviewRequest = () => {
	const { settings, updateSetting } = useSettingsContext<AllSettings>();

	if (settings._kudos_review_request_dismissed) {
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
