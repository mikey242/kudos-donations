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
					'default'      => 'tls',
				],
				'smtp_autotls'          => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
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
					'type'         => 'string',
					'show_in_rest' => true,
				],
				'theme_colors'          => [
					'type'         => 'object',
					'default'      =>  [
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
					]
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
				'disable_object_cache'  => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => false,
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
									'protected'        => [
										'type' => 'boolean',
									],
								],
							],
						],
					],
					'default' => [
						0 => [
							'id'               => 'default',
							'name'             => 'Default',
							'modal_title'      => __( 'Support us!', 'kudos-donations' ),
							'welcome_text'     => __( 'Your support is greatly appreciated and will help to keep us going.',
								'kudos-donations' ),
							'address_required' => true,
							'amount_type'      => 'both',
							'fixed_amounts'    => '1,5,20,50',
							'donation_type'    => 'both',
							'protected'        => true,
						],
					],
					'sanitize_callback' => [ new Campaigns(), 'sanitize_campaigns' ],
				],
			]
		);
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

}
