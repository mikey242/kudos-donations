<?php

namespace Kudos\Helpers;

class Settings {

	const PREFIX = '_kudos_';

	/**
	 * Settings configuration
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Settings class constructor.
	 *
	 * @since   2.0.0
	 */
	public function __construct() {

		$this->settings = apply_filters(
			'kudos_register_settings',
			[
				'show_intro'            => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
				],
				'mollie_connected'      => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
				],
				'mollie_api_mode'       => [
					'type'         => 'string',
					'show_in_rest' => true,
					'default'      => 'test',
				],
				'mollie_test_api_key'   => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'mollie_live_api_key'   => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'email_receipt_enable'  => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
				],
				'email_bcc'             => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_email',
				],
				'smtp_enable'           => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
				],
				'smtp_host'             => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'smtp_encryption'       => [
					'type'         => 'string',
					'show_in_rest' => true,
				],
				'smtp_autotls'          => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
				],
				'smtp_from'             => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_email',
				],
				'smtp_username'         => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'smtp_password'         => [
					'type'         => 'string',
					'show_in_rest' => true,
				],
				'smtp_port'             => [
					'type'         => 'string',
					'show_in_rest' => true,
				],
				'theme_color'           => [
					'type'         => 'string',
					'show_in_rest' => true,
					'default'      => '#ff9f1c',
				],
				'address_enabled'       => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
				],
				'address_required'      => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
				],
				'terms_link'            => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => null,
					'sanitize_callback' => 'esc_url_raw',
				],
				'return_message_enable' => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
				],
				'return_message_title'  => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => __( 'Thank you!', 'kudos-donations' ),
					'sanitize_callback' => 'sanitize_text_field',
				],
				'return_message_text'   => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => sprintf(
					/* translators: %s: Value of donation. */
						__( 'Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations' ),
						'{{value}}'
					),
					'sanitize_callback' => 'sanitize_text_field',
				],
				'custom_return_enable'  => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
				],
				'custom_return_url'     => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'esc_url_raw',
				],
				'payment_vendor'        => [
					'type'    => 'string',
					'default' => 'mollie',
				],
				'debug_mode'            => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
				],
				'campaigns'        => [
					'type'         => 'array',
					'show_in_rest' => [
						'schema' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'slug' => [
										'type' => 'string'
									],
									'name' => [
										'type' => 'string'
									],
									'modal_title' => [
										'type' => 'string'
									],
									'welcome_text' => [
										'type' => 'string',
									],
									'address_enabled'  => [
										'type' => 'boolean',
									],
									'address_required' => [
										'type' => 'boolean',
									],
									'amount_type'   => [
										'type' => 'string',
									],
									'fixed_amounts' => [
										'type' => 'string',
									],
									'donation_type'    => [
										'type' => 'string',
									],
								],
							],
						],
					],
					'sanitize_callback' => [$this, 'sanitize_campaigns'],
				],
			]
		);
	}

	/**
	 * Sanitize the various setting fields in the donation form array
	 *
	 * @param $forms
	 *
	 * @return array
	 * @since 2.3.0
	 */
	public static function sanitize_campaigns($forms): array {

		//Define the array for the updated options
		$output = [];

		// Loop through each of the options sanitizing the data
		foreach ($forms as $key=>$form) {

			foreach ($form as $option=>$value) {
				switch ($option) {
					case 'address_enabled':
					case 'address_required':
					case 'amount_type':
					case 'donation_type':
						$output[$key][$option] = sanitize_key($value);
						break;
					case 'slug':
						$output[$key][$option] = sanitize_title($value);
						break;
					default:
						$output[$key][$option] = sanitize_text_field($value);
				}
			}

		}

		return $output;
	}

	/**
	 * Returns setting value
	 *
	 * @param string $name Setting name.
	 *
	 * @return mixed
	 * @since   2.0.0
	 */
	public static function get_setting( string $name ) {

		return get_option( self::PREFIX . $name );

	}

	/**
	 * Gets the campaign by slug name
	 *
	 * @param string $slug
	 *
	 * @return array|null
	 */
	public static function get_campaign( string $slug ): ?array {

		$forms = self::get_setting('campaigns');
		$key = array_search($slug, array_column($forms, 'slug'));

		if(is_int($key)) {
			return $forms[$key];
		}

		return null;

	}

	/**
	 * Update specified setting
	 *
	 * @param string $name Setting name.
	 * @param mixed $value Setting value.
	 *
	 * @return bool
	 * @since 2.0.4
	 */
	public static function update_setting( string $name, $value ): bool {

		return update_option( self::PREFIX . $name, $value );

	}

	/**
	 * Register all the settings
	 *
	 * @since   2.0.0
	 */
	public function register_settings() {

		foreach ( $this->settings as $name => $setting ) {
			register_setting(
				'kudos_donations',
				self::PREFIX . $name,
				$setting
			);
		}

	}

	/**
	 * Add the settings to the database
	 *
	 * @since 2.0.0
	 */
	public function add_defaults() {

		foreach ( $this->settings as $name => $setting ) {
			add_option( self::PREFIX . $name, $setting['default'] ?? '' );
		}

	}

	/**
	 * Removes all settings from database
	 *
	 * @since 2.0.0
	 */
	public function remove_settings() {

		foreach ( $this->settings as $key => $setting ) {
			self::remove_setting( $key );
		}

	}

	/**
	 * Remove specified setting from database
	 *
	 * @param string $name
	 *
	 * @return bool
	 * @since 2.1.1
	 */
	public static function remove_setting( string $name ): bool {

		return delete_option( self::PREFIX . $name );

	}

}
