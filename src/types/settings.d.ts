export interface VendorPaymentMethod {
	id: string;
	label: string;
}

// Setup steps declared by the active payment provider, rendered by the onboarding banner.
export interface VendorOnboardingStep {
	id: string;
	label: string;
	done: boolean;
	panel: string;
}

export interface VendorStatus {
	ready: boolean;
	recurring: boolean;
	account?: string;
	steps?: VendorOnboardingStep[];
	methods?: VendorPaymentMethod[];
}

export interface BaseSettings {
	_kudos_always_load_assets: boolean;
	_kudos_invoice_number: number;
	_kudos_invoice_company_address: string;
	_kudos_invoice_vat_number: string;
	_kudos_email_vendor: 'smtp' | 'mail' | string; // 'smtp' was used, 'mail' possible
	_kudos_email_receipt_enable: boolean;
	_kudos_email_show_campaign_name: boolean;
	_kudos_db_version: string;
	_kudos_donations_version: string;
	_kudos_migration_status: string[];
	_kudos_payment_vendor: 'mollie' | 'stripe' | 'demo' | string;
	_kudos_payment_vendor_status: VendorStatus;
	_kudos_debug_mode: boolean;
	_kudos_base_font_size: string; // e.g., '1.2rem'
	_kudos_maximum_donation: number;
	_kudos_allow_metrics: boolean;
	_kudos_email_bcc: string;
	_kudos_custom_smtp: string | null;
	_kudos_smtp_password: string;
	_kudos_smtp_enable: boolean;
	_kudos_onboarding_dismissed: boolean;
}
