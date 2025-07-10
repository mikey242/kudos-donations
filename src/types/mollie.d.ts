import { BaseSettings } from './settings';

export interface MolliePaymentMethod {
	id: string;
	description: string;
	image: string;
	minimumAmount: {
		value: string;
		currency: string;
	};
	maximumAmount:
		| {
		value: string;
		currency: string;
	}
		| []; // some were empty arrays
}

export interface MollieProfile {
	id: string;
	mode: string;
	name: string;
	website: string;
	string: string;
}

export interface MollieSettings extends BaseSettings {
	_kudos_vendor_mollie_api_mode: 'test' | 'live';
	_kudos_vendor_mollie_api_key_test: string;
	_kudos_vendor_mollie_api_key_live: string;
	_kudos_vendor_mollie_recurring: boolean;
	_kudos_vendor_mollie_profile: MollieProfile;
	_kudos_vendor_mollie_payment_methods: MolliePaymentMethod[];
}