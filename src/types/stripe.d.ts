import type { BaseSettings } from './settings';

export interface StripeWebhook {
	secret?: string;
	endpoint_id?: string;
}

export interface StripeSettings extends BaseSettings {
	_kudos_vendor_stripe_api_mode: 'test' | 'live';
	_kudos_vendor_stripe_api_key_test: string;
	_kudos_vendor_stripe_api_key_live: string;
	_kudos_vendor_stripe_webhook: StripeWebhook;
}