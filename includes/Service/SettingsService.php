<?php
/**
 * Settings Service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

class SettingsService extends AbstractService {

	public const HOOK_GET_SETTINGS                 = 'kudos_get_settings';
	public const SETTING_GROUP                     = 'kudos-donations';
	public const SETTING_NAME_SHOW_INTRO           = '_kudos_show_intro';
	public const SETTING_NAME_VENDOR               = '_kudos_vendor';
	public const SETTING_NAME_VENDOR_MOLLIE        = '_kudos_vendor_mollie';
	public const SETTING_NAME_EMAIL_RECEIPT_ENABLE = '_kudos_email_receipt_enable';
	public const SETTING_NAME_EMAIL_BCC            = '_kudos_email_bcc';
	public const SETTING_NAME_CUSTOM_SMTP          = '_kudos_custom_smtp';
	public const SETTING_NAME_SMTP_ENABLE          = '_kudos_smtp_enable';
	public const SETTING_NAME_SPAM_PROTECTION      = '_kudos_spam_protection';
	public const SETTING_NAME_DEBUG_MODE           = '_kudos_debug_mode';
	public const SETTING_NAME_ALWAYS_LOAD_ASSETS   = '_kudos_always_load_assets';
	public const SETTING_NAME_DB_VERSION           = '_kudos_db_version';
	public const SETTING_NAME_MIGRATION_HISTORY    = '_kudos_migration_history';
	public const SETTING_NAME_INVOICE_NUMBER       = '_kudos_invoice_number';

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_all();
	}

	/**
	 * Registers all settings.
	 */
	public function register_all(): void {
		foreach ( $this->get_settings() as $name => $args ) {
			register_setting(
				self::SETTING_GROUP,
				$name,
				$args
			);
		}
	}

	/**
	 * Returns the value for a given setting.
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default_value Optional. Default value to return if the option does not exist.
	 * @return mixed
	 */
	public function get_setting( string $key, $default_value = false ) {
		// Distinguish between `false` as a default, and not passing one, just like WordPress.
		$passed_default = \func_num_args() > 1;

		if ( $passed_default ) {
			$option = get_option( $key, $default_value );
			if ( $option === $default_value ) {
				return $option;
			}
		} else {
			$option = get_option( $key );
		}

		$settings = $this->get_registered_options();
		if ( isset( $settings[ $key ] ) ) {
			$value = rest_sanitize_value_from_schema( $option, $settings[ $key ] );
			if ( is_wp_error( $value ) ) {
				return $option;
			}
			$option = $value;
		}

		return $option;
	}

	/**
	 * Returns the settings for the current vendor.
	 *
	 * @return mixed
	 */
	public function get_current_vendor_settings() {
		$vendor = $this->get_setting( self::SETTING_NAME_VENDOR );
		return $this->get_setting( '_kudos_vendor_' . $vendor );
	}

	/**
	 * Updates the given setting with a new value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool Setting value.
	 */
	public function update_setting( string $key, $value ): bool {
		return update_option( $key, $value );
	}

	/**
	 * Retrieves all the registered options for the Settings API.
	 * Inspired by get_registered_options method found in WordPress. But also get settings that are registered without `show_in_rest` property.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/rest-api/endpoints/class-wp-rest-settings-controller.php#L211-L267
	 *
	 * @return array<string, array<string,string>> Array of registered options.
	 */
	protected function get_registered_options(): array {
		$rest_options = [];

		foreach ( get_registered_settings() as $name => $args ) {
			$rest_args = [];

			if ( ! empty( $args['show_in_rest'] ) && \is_array( $args['show_in_rest'] ) ) {
				$rest_args = $args['show_in_rest'];
			}

			$defaults = [
				'name'   => ! empty( $rest_args['name'] ) ? $rest_args['name'] : $name,
				'schema' => [],
			];

			$rest_args = array_merge( $defaults, $rest_args );

			$default_schema = [
				'type'        => empty( $args['type'] ) ? null : $args['type'],
				'description' => empty( $args['description'] ) ? '' : $args['description'],
				'default'     => $args['default'] ?? null,
			];

			$schema = array_merge( $default_schema, $rest_args['schema'] );
			$schema = rest_default_additional_properties_to_false( $schema );

			$rest_options[ $name ] = $schema;
		}

		return $rest_options;
	}

	/**
	 * Returns all settings in array.
	 */
	public static function get_settings(): array {
		return apply_filters(
			self::HOOK_GET_SETTINGS,
			[
				self::SETTING_NAME_SHOW_INTRO           => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_NAME_VENDOR               => [
					'type'         => 'string',
					'show_in_rest' => true,
					'default'      => 'mollie',
				],
				self::SETTING_NAME_VENDOR_MOLLIE        => [
					'type'         => 'object',
					'default'      => [
						'recurring'       => false,
						'mode'            => 'test',
						'payment_methods' => [],
						'test_key'        => [
							'verified' => false,
						],
						'live_key'        => [
							'verified' => false,
						],
					],
					'show_in_rest' => [
						'schema' => [
							'type'       => 'object',
							'properties' => [
								'recurring'       => [
									'type' => 'boolean',
								],
								'mode'            => [
									'type' => 'string',
								],
								'test_key'        => [
									'type'       => 'object',
									'properties' => [
										'key'      => [
											'type' => 'string',
										],
										'verified' => [
											'type' => 'boolean',
										],
									],
								],
								'live_key'        => [
									'type'       => 'object',
									'properties' => [
										'key'      => [
											'type' => 'string',
										],
										'verified' => [
											'type' => 'boolean',
										],
									],
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
											'maximumAmount' => [
												'type' => 'object',
												'properties' => [
													'value'    => [
														'type' => 'string',
													],
													'currency' => [
														'type' => 'string',
													],
												],
											],
										],
									],
								],
							],
						],
					],
				],
				self::SETTING_NAME_EMAIL_RECEIPT_ENABLE => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_NAME_EMAIL_BCC            => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_email',
				],
				self::SETTING_NAME_CUSTOM_SMTP          => [
					'type'         => 'object',
					'default'      => [
						'from_email' => '',
						'from_name'  => get_bloginfo( 'name' ),
						'host'       => '',
						'port'       => '',
						'encryption' => 'tls',
						'autotls'    => false,
						'username'   => '',
						'password'   => '',
					],
					'show_in_rest' => [
						'schema' => [
							'type'       => 'object',
							'properties' => [
								'from_email' => [
									'type' => 'string',
								],
								'from_name'  => [
									'type' => 'string',
								],
								'host'       => [
									'type' => 'string',
								],
								'port'       => [
									'type' => 'number',
								],
								'encryption' => [
									'type' => 'string',
								],
								'autotls'    => [
									'type' => 'boolean',
								],
								'username'   => [
									'type' => 'string',
								],
								'password'   => [
									'type' => 'string',
								],
							],
						],
					],
				],
				self::SETTING_NAME_SMTP_ENABLE          => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_NAME_SPAM_PROTECTION      => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_NAME_DEBUG_MODE           => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_NAME_ALWAYS_LOAD_ASSETS   => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_NAME_INVOICE_NUMBER       => [
					'type'              => 'int',
					'show_in_rest'      => false,
					'default'           => 1,
					'sanitize_callback' => 'absint',
				],
			]
		);
	}

	/**
	 * Method to recursively sanitize all text fields in an array.
	 *
	 * @link https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
	 *
	 * @param array $values Array of values to sanitize.
	 */
	public static function recursive_sanitize_text_field( array $values ): array {
		foreach ( $values as &$value ) {
			if ( \is_array( $value ) ) {
				$value = self::recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $values;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_init', 'rest_api_init', 'init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 5;
	}
}
