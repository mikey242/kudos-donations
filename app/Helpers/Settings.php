<?php

namespace Kudos\Helpers;

class Settings {

	const PREFIX = '_kudos_';

	/**
	 * Settings configuration
	 * @var array
	 */
	private $settings;

	/**
	 * Settings class constructor.
	 *
	 * @since   2.0.0
	 */
	public function __construct() {

		$this->settings = apply_filters( 'kudos_register_settings',
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
				'subscription_enabled'  => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
				],
				'privacy_link'          => [
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
					'default'           => sprintf( __( 'Many thanks for your donation of %s. We appreciate your support.',
						'kudos-donations' ),
						'{{value}}' ),
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
				'action_scheduler'      => [
					'type'         => 'boolean',
					'show_in_rest' => true,
					'default'      => true,
				],
			] );

	}

	/**
	 * Returns setting value
	 *
	 * @param $name
	 *
	 * @return false|mixed|void
	 * @since   2.0.0
	 */
	public static function get_setting( $name ) {

		return get_option( self::PREFIX . $name );

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
			delete_option( self::PREFIX . $key );
		}

	}

}
