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
	_kudos_payment_vendor: 'mollie' | 'paypal' | string;
	_kudos_show_intro: boolean | null;
	_kudos_debug_mode: boolean;
	_kudos_base_font_size: string; // e.g., '1.2rem'
	_kudos_maximum_donation: number;
	_kudos_allow_metrics: boolean;
	_kudos_email_bcc: string;
	_kudos_custom_smtp: string | null;
	_kudos_smtp_password: string;
	_kudos_smtp_enable: boolean;
	_kudos_payment_vendor_status: {
		ready: boolean;
		recurring: boolean;
		text: string;
	};
}
