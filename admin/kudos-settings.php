<?php

// Mollie Settings

register_setting(
	'kudos_donations',
	'_kudos_mollie_connected',
	[
		'type'         => 'boolean',
		'show_in_rest' => true,
		'default'      => false
	]
);

register_setting(
	'kudos_donations',
	'_kudos_mollie_api_mode',
	[
		'type'         => 'string',
		'show_in_rest' => true,
		'default'      => 'test',
	]
);

register_setting(
	'kudos_donations',
	'_kudos_mollie_test_api_key',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_mollie_live_api_key',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

// Email Settings

register_setting(
	'kudos_donations',
	'_kudos_email_receipt_enable',
	[
		'type'         => 'boolean',
		'show_in_rest' => true,
		'default'      => false,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_email_bcc',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_enable',
	[
		'type'         => 'boolean',
		'show_in_rest' => true,
		'default'      => false,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_host',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_encryption',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_autotls',
	[
		'type'         => 'boolean',
		'show_in_rest' => true,
		'default'      => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_username',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_password',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

register_setting(
	'kudos_donations',
	'_kudos_smtp_port',
	[
		'type'         => 'string',
		'show_in_rest' => true,
	]
);

// Donation button settings

register_setting(
	'kudos_donations',
	'_kudos_button_label',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => __('Donate now', 'kudos-donations')
	]
);

register_setting(
	'kudos_donations',
	'_kudos_button_color',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => '#ff9f1c'
	]
);

// Donation form

register_setting(
	'kudos_donations',
	'_kudos_address_required',
	[
		'type'          => 'boolean',
		'show_in_rest'  => true,
		'default'       => false
	]
);

register_setting(
	'kudos_donations',
	'_kudos_form_header',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => __('Support us!', 'kudos-donations')
	]
);

register_setting(
	'kudos_donations',
	'_kudos_form_text',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => __('Thank you for your donation. We appreciate your support!', 'kudos-donations')
	]
);

register_setting(
	'kudos_donations',
	'_kudos_privacy_link',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => null
	]
);

// Completed payment settings

register_setting(
	'kudos_donations',
	'_kudos_return_message_enable',
	[
		'type'          => 'boolean',
		'show_in_rest'  => true,
		'default'       => true
	]
);

register_setting(
	'kudos_donations',
	'_kudos_return_message_header',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => __('Thank you!', 'kudos-donations')
	]
);

register_setting(
	'kudos_donations',
	'_kudos_return_message_text',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => sprintf(__('Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations'), '{{value}}')
	]
);

register_setting(
	'kudos_donations',
	'_kudos_custom_return_enable',
	[
		'type'          => 'boolean',
		'show_in_rest'  => true,
		'default'       => false
	]
);

register_setting(
	'kudos_donations',
	'_kudos_custom_return_url',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
	]
);

// Invoice settings

register_setting(
	'kudos_donations',
	'_kudos_invoice_company_name',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => get_bloginfo('name')
	]
);

register_setting(
	'kudos_donations',
	'_kudos_invoice_company_address',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => ''
	]
);

register_setting(
	'kudos_donations',
	'_kudos_invoice_vat_number',
	[
		'type'          => 'string',
		'show_in_rest'  => true,
		'default'       => ''
	]
);

register_setting(
	'kudos_donations',
	'_kudos_invoice_enable',
	[
		'type'          => 'boolean',
		'show_in_rest'  => true,
		'default'       => false
	]
);

register_setting(
	'kudos_donations',
	'_kudos_attach_invoice',
	[
		'type'          => 'boolean',
		'show_in_rest'  => true,
		'default'       => false
	]
);