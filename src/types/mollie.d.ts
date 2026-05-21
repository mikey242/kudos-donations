import { BaseSettings } from './settings';

export interface MollieSettings extends BaseSettings {
	_kudos_vendor_mollie_api_mode: 'test' | 'live';
	_kudos_vendor_mollie_api_key_test: string;
	_kudos_vendor_mollie_api_key_live: string;
}
