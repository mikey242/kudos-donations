<?php

class Settings {

	const CLASSIC = 0;
	const PRO = 1;
	const PLUS = 2;
	const PLUSPLUS = 3;

	/**
	 * Settings configuration
	 * @var array
	 */
	private static $settings;

	/**
	 * Settings init.
	 *
	 * @since   2.0.0
	 */
	public static function init() {

		self::$settings = [
			'_kudos_mollie_connected' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false
			],
			'_kudos_mollie_api_mode' => [
				'type'         => 'string',
				'show_in_rest' => true,
				'default'      => 'test',
			],
			'_kudos_mollie_test_api_key' =>[
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_email_receipt_enable' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false,
			],
			'_kudos_email_bcc' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_smtp_enable' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false,
			],
			'_kudos_smtp_host' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_smtp_encryption' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_smtp_autotls' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => true,
			],
			'_kudos_smtp_from' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_smtp_username' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_smtp_password' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_smtp_port' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'_kudos_button_label' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Donate now', 'kudos-donations')
			],
			'_kudos_button_color' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => '#ff9f1c'
			],
			'_kudos_address_enabled' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'_kudos_address_required' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'_kudos_form_header' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Support us!', 'kudos-donations')
			],
			'_kudos_form_text' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Thank you for your donation. We appreciate your support!', 'kudos-donations')
			],
			'_kudos_privacy_link' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => null
			],
			'_kudos_amount_type' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'level' => self::PRO,
				'default' => 'open'
			],
			'_kudos_fixed_amounts' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'level'         => self::PRO,
				'default'       => '5, 10, 20, 50'
			],
			'_kudos_return_message_enable' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'_kudos_return_message_header' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Thank you!', 'kudos-donations')
			],
			'_kudos_return_message_text' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => sprintf(__('Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations'), '{{value}}')
			],
			'_kudos_custom_return_enable' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'_kudos_custom_return_url' => [
				'type'          => 'string',
				'show_in_rest'  => true,
			],
			'_kudos_invoice_company_name' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => get_bloginfo('name')
			],
			'_kudos_invoice_company_address' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => ''
			],
			'_kudos_invoice_vat_number' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => ''
			],
			'_kudos_invoice_enable' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'_kudos_attach_invoice' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'_kudos_debug_mode' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'_kudos_action_scheduler' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'_kudos_subscription_level' => [
				'type'          => 'integer',
				'show_in_rest'  => true,
				'default'       => 0
			]
		];

	}

	/**
	 * Gets the setting from the database, if the current subscription level is lower
	 * then returns default value.
	 *
	 * @param $name
	 * @return false|mixed|void
	 * @since   2.0.0
	 */
	public static function get_setting($name) {

		$settings = self::$settings;

		if(!empty($settings[$name]['level']) && $settings[$name]['level'] > get_option('_kudos_subscription_level')) {
			return $settings[$name]['default'];
		}

		return get_option($name);

	}

	/**
	 * Register all the settings
	 *
	 * @since   2.0.0
	 */
	public static function register_settings() {

		$settings = self::$settings;

		foreach ($settings as $name=>$setting) {
			register_setting(
				'kudos_donations',
				$name, $setting
			);
		}
	}

	/**
	 * Removes settings from database
	 *
	 * @since 2.0.0
	 */
	public function remove_settings() {

		$settings = self::$settings;
		foreach ( $settings as $key=>$setting ) {
			delete_option($key);
		}

	}

}

Settings::init();

