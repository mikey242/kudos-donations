import type { Post as WPPost } from '@wordpress/core-data';

export interface Transaction extends Omit<Post, 'meta'> {
	meta: {
		value: number;
		currency: string;
		status: 'open' | 'paid' | 'failed' | 'canceled' | string;
		method: string;
		mode: 'test' | 'live' | string;
		sequence_type: 'oneoff' | 'first' | 'recurring' | string;
		donor_id: number;
		vendor_payment_id: string;
		campaign_id: number;
		refunds: string;
		message: string;
		vendor: string;
		vendor_customer_id: string;
		vendor_subscription_id: string;
		invoice_number: number;
		checkout_url: string;
	};
	donor: Donor;
	campaign: Campaign;
	total: string;
	invoice_url: string;
}

export interface Subscription extends Omit<Post, 'meta'> {
	meta: {
		value: number;
		currency: string;
		frequency: string;
		years: number;
		status: string;
		customer_id: string;
		transaction_id: string;
		vendor_subscription_id: string;
	};
	donor: Donor;
	campaign: Campaign;
}

export interface Donor extends Omit<Post, 'meta'> {
	meta: {
		email: string;
		mode: string;
		name: string;
		business_name: string;
		street: string;
		postcode: string;
		city: string;
		country: string;
		vendor_customer_id: string;
	};
	total: string;
}

export interface Campaign extends Omit<Post, 'meta'> {
	meta: {
		currency: string;
		goal?: number;
		show_goal: boolean;
		additional_funds: string;
		initial_title: string;
		initial_description: string;
		subscription_title: string;
		subscription_description: string;
		address_title: string;
		address_description: string;
		message_title: string;
		message_description: string;
		payment_title: string;
		payment_description: string;
		address_enabled: boolean;
		address_required: boolean;
		message_enabled: boolean;
		amount_type: 'fixed' | 'open' | 'both';
		fixed_amounts: string[];
		minimum_donation: number;
		maximum_donation: number;
		donation_type: 'one-off' | 'recurring' | 'both';
		theme_color: string;
		terms_link: string;
		privacy_link: string;
		show_return_message: boolean;
		use_custom_return_url: boolean;
		custom_return_url: string;
		return_message_title: string;
		return_message_text: string;
		custom_styles: string;
		allow_anonymous: boolean;
		payment_description_format: string;
		frequency_options: Record<string, string>;
		[key: string]: unknown;
	};
	total: number;
}

export interface Post extends Omit<WPPost, 'meta'> {
	meta?: Record<string, unknown>;
}