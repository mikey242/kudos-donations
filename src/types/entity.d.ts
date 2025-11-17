export interface Transaction extends BaseEntity {
	value: number;
	currency: string;
	status: 'open' | 'paid' | 'failed' | 'canceled' | string;
	method?: string;
	mode: 'test' | 'live' | string;
	sequence_type?: 'oneoff' | 'first' | 'recurring' | string;
	donor_id?: number;
	campaign_id?: number;
	vendor?: string;
	vendor_payment_id?: string;
	vendor_customer_id?: string;
	vendor_subscription_id?: string;
	invoice_number?: number;
	checkout_url?: string;
	message?: string;
	refunds?: string;
	invoice_url?: string;
	donor: Donor;
	campaign: Campaign;
}

export interface Subscription extends BaseEntity {
	value: number;
	currency: string;
	frequency: string;
	years?: number;
	status: string;
	customer_id: string;
	transaction_id?: number;
	vendor_subscription_id?: string;
	donor?: Donor;
	campaign?: Campaign;
    token: string;
}

export interface Donor extends BaseEntity {
	email: string;
	mode: 'test' | 'live' | string;
	name?: string;
	business_name?: string;
	street?: string;
	postcode?: string;
	city?: string;
	country?: string;
	vendor_customer_id?: string;
	total?: string;
}

export interface Campaign extends BaseEntity {
	currency: string;
	goal?: number;
	show_goal: boolean;
	additional_funds?: string;
	amount_type: 'fixed' | 'open' | 'both';
	fixed_amounts?: string[];
	minimum_donation?: number;
	maximum_donation?: number;
	donation_type: 'one-off' | 'recurring' | 'both';
	email_enabled: boolean;
	email_required: boolean;
	name_enabled: boolean;
	name_required: boolean;
	address_enabled: boolean;
	address_required: boolean;
	message_enabled: boolean;
	message_required: boolean;
	theme_color?: string;
	terms_link?: string;
	privacy_link?: string;
	show_return_message?: boolean;
	use_custom_return_url?: boolean;
	custom_return_url?: string;
	payment_description_format?: string;
	custom_styles?: string;
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
	frequency_options?: Record<string, string>;
	return_message_title?: string;
	return_message_text?: string;
	total?: number;
}

export interface BaseEntity {
	id: number;
	wp_post_id: number;
	title: string;
	created_at: string;
	updated_at?: string | null;
}