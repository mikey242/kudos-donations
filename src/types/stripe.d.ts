import type { BaseSettings } from './settings';

export interface StripeWebhook {
	secret?: string;
	endpoint_id?: string;
}

// Endpoints are registered per API mode: a test endpoint cannot verify live events.
export interface StripeWebhooks {
	test?: StripeWebhook;
	live?: StripeWebhook;
}

export interface StripeSettings extends BaseSettings {
	_kudos_vendor_stripe_api_mode: 'test' | 'live';
	_kudos_vendor_stripe_api_key_test: string;
	_kudos_vendor_stripe_api_key_live: string;
	_kudos_vendor_stripe_webhook: StripeWebhooks;
}
