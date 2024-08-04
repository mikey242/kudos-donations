import { __ } from '@wordpress/i18n';

// Form router config.
export const steps = {
	1: {
		name: 'Initial',
	},
	2: {
		name: 'Recurring',
		requirements: {
			recurring: true,
		},
	},
	3: {
		name: 'Address',
		requirements: {
			address_enabled: true,
		},
	},
	4: {
		name: 'Message',
		requirements: {
			message_enabled: true,
		},
	},
	5: {
		name: 'Summary',
	},
};

// Set tabs height according to the highest tab.
export function setFormHeight(form) {
	if (!form.closest('.kudos-modal')) {
		const tabs = form.querySelectorAll('fieldset');
		const array = Array.from(tabs).map((tab) => {
			return tab.offsetHeight;
		});
		const max = Math.max.apply(Math, array);

		form.style.minHeight = max + 'px';
	}
}

export function getFrequencyName(frequency) {
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

export function checkRequirements(data, target) {
	const reqs = steps[target].requirements;
	if (reqs) {
		// Requirements found for target
		for (const [key, value] of Object.entries(reqs)) {
			// Iterate through requirements and check if they match data
			if (value !== data[key]) {
				// Requirement not satisfied, not OK to proceed
				return false;
			}
		}
		return true;
	}
	// No requirements found, OK to proceed
	return true;
}
