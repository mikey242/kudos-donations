import { __ } from '@wordpress/i18n';

/**
 * Returns the display name for a payment frequency, such as "Monthly" for "1 month".
 *
 * @param frequency The frequency as stored, e.g. '1 month', '3 months' or '12 months'.
 */
export function getFrequencyName(frequency: string): string {
	switch (frequency) {
		case '12 months':
			return __('Yearly', 'kudos-donations');
		case '1 month':
			return __('Monthly', 'kudos-donations');
		case '3 months':
			return __('Quarterly', 'kudos-donations');
		case 'oneoff':
			return __('One-off', 'kudos-donations');
		default:
			return frequency;
	}
}
