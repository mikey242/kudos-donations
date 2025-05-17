import type { Post as WPPost } from '@wordpress/core-data';

export interface CampaignMeta {
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
}

export interface Post extends Omit<WPPost, 'meta'> {
	meta?: Record<string, unknown>;
}

export interface Campaign extends Omit<Post, 'meta'> {
	meta: CampaignMeta;
	total: number;
}

export interface WPResponse {
	message: string;
}

export interface WPErrorResponse extends WPResponse {
	code: string;
	data?: {
		status?: number;
		[key: string]: any;
	};
}
