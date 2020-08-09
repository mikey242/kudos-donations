<?php

class Settings {

	const PREFIX = '_kudos_';

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
			'mollie_connected' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false
			],
			'mollie_api_mode' => [
				'type'         => 'string',
				'show_in_rest' => true,
				'default'      => 'test',
			],
			'mollie_test_api_key' =>[
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'email_receipt_enable' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false,
			],
			'email_bcc' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'smtp_enable' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false,
			],
			'smtp_host' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'smtp_encryption' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'smtp_autotls' => [
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => true,
			],
			'smtp_from' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'smtp_username' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'smtp_password' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'smtp_port' => [
				'type'         => 'string',
				'show_in_rest' => true,
			],
			'button_label' => [
				'type'          => 'string',
				'default'       => __('Donate now', 'kudos-donations')
			],
			'theme_color' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => '#ff9f1c'
			],
			'address_enabled' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'address_required' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'modal_header' => [
				'type'          => 'string',
				'default'       => __('Support us!', 'kudos-donations')
			],
			'welcome_text' => [
				'type'          => 'string',
				'default'       => __('Thank you for your donation. We appreciate your support!', 'kudos-donations')
			],
			'privacy_link' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => null
			],
			'amount_type' => [
				'type'          => 'string',
				'default'       => 'open'
			],
			'fixed_amounts' => [
				'type'          => 'string',
				'default'       => '5, 10, 20, 50'
			],
			'return_message_enable' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'return_message_header' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Thank you!', 'kudos-donations')
			],
			'return_message_text' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => sprintf(__('Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations'), '{{value}}')
			],
			'custom_return_enable' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'custom_return_url' => [
				'type'          => 'string',
				'show_in_rest'  => true,
			],
			'invoice_company_name' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => get_bloginfo('name')
			],
			'invoice_company_address' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => ''
			],
			'invoice_vat_number' => [
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => ''
			],
			'invoice_enable' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			],
			'attach_invoice' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'payment_vendor' => [
				'type'          => 'string',
				'default'       => 'mollie'
			],
			'debug_mode' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			],
			'action_scheduler' => [
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
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

		return get_option(self::PREFIX . $name);

	}

	/**
	 * Register all the settings
	 *
	 * @since   2.0.0
	 */
	public static function register_settings() {

		foreach (self::$settings as $name=>$setting) {
			register_setting(
				'kudos_donations',
				self::PREFIX . $name, $setting
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

