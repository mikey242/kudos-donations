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
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'vendor_mollie'         => [
					'type'         => 'object',
					'default'      => [
						'connected'       => false,
						'recurring'       => false,
						'mode'            => 'test',
						'payment_methods' => [],
						'test_key'        => '',
						'live_key'        => '',
					],
					'show_in_rest' => [
						'schema' => [
							'type'       => 'object',
							'properties' => [
								'connected'       => [
									'type' => 'boolean',
								],
								'recurring'       => [
									'type' => 'boolean',
								],
								'mode'            => [
									'type' => 'string',
								],
								'test_key'        => [
									'type' => 'string',
								],
								'live_key'        => [
									'type' => 'string',
								],
								'payment_methods' => [
									'type'  => 'array',
									'items' => [
										'type'       => 'object',
										'properties' => [
											'id'     => [
												'type' => 'string',
											],
											'status' => [
												'type' => 'string',
											],
										],
									],
								],
							],
						],
					],
					'sanitize_callback' => [ $this, 'sanitize_vendor' ],
				],
				'email_receipt_enable'  => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'email_bcc'             => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_email',
				],
				'smtp_enable'           => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'smtp_host'             => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'smtp_encryption'       => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => 'tls',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'smtp_autotls'          => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'smtp_from'             => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => null,
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
					'type'              => 'number',
					'show_in_rest'      => true,
					'sanitize_callback' => 'intval'
				],
				'theme_colors'          => [
					'type'         => 'object',
					'default'      => [
						'primary'   => '#ff9f1c',
						'secondary' => '#2ec4b6',
					],
					'show_in_rest' => [
						'schema' => [
							'type'       => 'object',
							'properties' => [
								'primary'   => [
									'type' => 'string',
								],
								'secondary' => [
									'type' => 'string',
								],
							],
						],
					],
					'sanitize_callback' => [$this, 'recursive_sanitize_text_field']
				],
				'address_enabled'       => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'address_required'      => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'terms_link'            => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => null,
					'sanitize_callback' => 'esc_url_raw',
				],
				'privacy_link'          => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'default'           => null,
					'sanitize_callback' => 'esc_url_raw',
				],
				'return_message_enable' => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
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
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
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
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'disable_object_cache'  => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'campaigns'             => [
					'type'              => 'array',
					'show_in_rest'      => [
						'schema' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'id'               => [
										'type' => 'string',
									],
									'name'             => [
										'type' => 'string',
									],
									'campaign_goal'    => [
										'type' => 'string',
									],
									'show_progress'    => [
										'type' => 'boolean',
									],
									'modal_title'      => [
										'type' => 'string',
									],
									'welcome_text'     => [
										'type' => 'string',
									],
									'address_enabled'  => [
										'type' => 'boolean',
									],
									'address_required' => [
										'type' => 'boolean',
									],
									'amount_type'      => [
										'type' => 'string',
									],
									'fixed_amounts'    => [
										'type' => 'string',
									],
									'donation_type'    => [
										'type' => 'string',
									],
									// Deprecated: do not use
									'protected'        => [
										'type' => 'boolean',
									],
								],
							],
						],
					],
					'default'           => [
						0 => [
							'id'               => 'default',
							'name'             => 'Default',
							'modal_title'      => __( 'Support us!', 'kudos-donations' ),
							'welcome_text'     => __( 'Your support is greatly appreciated and will help to keep us going.',
								'kudos-donations' ),
							'address_required' => true,
							'amount_type'      => 'both',
							'fixed_amounts'    => '1,5,20,50',
							'campaign_goal'    => '',
							'donation_type'    => 'oneoff',
						],
					],
					'sanitize_callback' => [ new Campaigns(), 'sanitize_campaigns' ],
				],
			]
		);
	}

	/**
	 * Sanitize vendor settings.
	 *
	 * @param $settings
	 * @return mixed
	 * @since 2.4.2
	 */
	public static function sanitize_vendor($settings) {
		foreach ($settings as $setting=>&$value) {
			switch ($setting) {
				case 'connected':
				case 'recurring':
					$value = rest_sanitize_boolean($value);
					break;
				case 'live_key':
				case 'test_key':
				case 'mode':
					$value = sanitize_text_field($value);
					break;
				case 'payment_methods':
					$value = self::recursive_sanitize_text_field($value);
					break;
			}
		}
		return $settings;
	}

	/**
	 * Gets the settings for the current vendor
	 *
	 * @return mixed
	 */
	public static function get_current_vendor_settings() {

		return self::get_setting( 'vendor_' . self::get_setting( 'payment_vendor' ) );

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
	 * Updates specific values in serialized settings array
	 * e.g update_array('my_setting', ['enabled' => false])
	 *
	 * @param string $name // Setting array name
	 * @param array $value // Array of name=>values in setting to update
	 *
	 * @return bool
	 * @since 2.3.8
	 */
	public static function update_array( string $name, array $value ): bool {

		// Grab current data
		$current = self::get_setting( $name );

		// Check if setting is either an array or null
		if ( is_array( $current ) || ! null ) {
			// Merge provided data and current data then update setting
			$new = wp_parse_args( $value, $current );

			return self::update_setting( $name, $new );
		}

		return false;

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
			if ( isset( $setting['default'] ) ) {
				add_option( self::PREFIX . $name, $setting['default'] );
			}
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

	/**
	 * Method to recursively sanitize all text fields in an array.
	 *
	 * @param array $array Array of values to sanitize
	 * @return mixed
	 * @since 2.4.2
	 * @source https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
	 */
	public static function recursive_sanitize_text_field( array $array ): array {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = self::recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}
		return $array;
	}

}
