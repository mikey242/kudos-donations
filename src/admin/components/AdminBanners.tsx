import React from 'react';
import { useSettingsContext } from '../contexts';
import { useHasLiveDonation } from '../hooks';
import type { AllSettings } from '../../types/all-settings';
import { OnboardingBanner } from './OnboardingBanner';
import { ReviewRequest } from './ReviewRequest';

/**
 * The banner at the top of the Kudos admin. Once the site has a live donation, setup is
 * evidently done: onboarding makes way for the review request, so the two never stack
 * and a completed but never dismissed onboarding banner doesn't hold the request back.
 */
export const AdminBanners = () => {
	const { settings } = useSettingsContext<AllSettings>();
	// Neither banner can show once both are dismissed; skip the request then.
	const hasLiveDonation = useHasLiveDonation(
		!(
			settings._kudos_onboarding_dismissed &&
			settings._kudos_review_request_dismissed
		)
	);

	if (hasLiveDonation === null) {
		return null;
	}

	return hasLiveDonation ? <ReviewRequest /> : <OnboardingBanner />;
};
