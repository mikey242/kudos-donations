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

use Exception;
use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Auth;

class SettingsService extends AbstractRegistrable {

	public const HOOK_GET_SETTINGS               = 'kudos_get_settings';
	public const GROUP                           = 'kudos-donations';
	public const SETTING_SHOW_INTRO              = '_kudos_show_intro';
	public const SETTING_VENDOR                  = '_kudos_vendor';
	public const SETTING_VENDOR_MOLLIE           = '_kudos_vendor_mollie';
	public const SETTING_EMAIL_RECEIPT_ENABLE    = '_kudos_email_receipt_enable';
	public const SETTING_EMAIL_BCC               = '_kudos_email_bcc';
	public const SETTING_CUSTOM_SMTP             = '_kudos_custom_smtp';
	public const SETTING_SMTP_ENABLE             = '_kudos_smtp_enable';
	public const SETTING_SPAM_PROTECTION         = '_kudos_spam_protection';
	public const SETTING_DEBUG_MODE              = '_kudos_debug_mode';
	public const SETTING_ALWAYS_LOAD_ASSETS      = '_kudos_always_load_assets';
	public const SETTING_DB_VERSION              = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY       = '_kudos_migration_history';
	public const SETTING_INVOICE_NUMBER          = '_kudos_invoice_number';
	public const SETTING_INVOICE_COMPANY_ADDRESS = '_kudos_invoice_company_address';
	public const SETTING_INVOICE_VAT_NUMBER      = '_kudos_invoice_vat_number';
	public const SETTING_SMTP_PASSWORD_ENCRYPTED = '_kudos_smtp_password_encrypted';

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
				self::GROUP,
				$name,
				$args
			);
		}
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

	/**
	 * Returns the value for a given setting.
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
		$vendor = $this->get_setting( self::SETTING_VENDOR );
		return $this->get_setting( '_kudos_vendor_' . $vendor );
	}

	/**
	 * Updates the given setting with a new value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True if the value was updated, false otherwise.
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
				self::SETTING_SHOW_INTRO              => [
					'type'              => FieldType::BOOLEAN,
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_VENDOR                  => [
					'type'         => FieldType::STRING,
					'show_in_rest' => true,
					'default'      => 'mollie',
				],
				self::SETTING_VENDOR_MOLLIE           => [
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
									'type' => FieldType::BOOLEAN,
								],
								'mode'            => [
									'type' => FieldType::STRING,
								],
								'test_key'        => [
									'type'       => 'object',
									'properties' => [
										'key'      => [
											'type' => FieldType::STRING,
										],
										'verified' => [
											'type' => FieldType::BOOLEAN,
										],
									],
								],
								'live_key'        => [
									'type'       => 'object',
									'properties' => [
										'key'      => [
											'type' => FieldType::STRING,
										],
										'verified' => [
											'type' => FieldType::BOOLEAN,
										],
									],
								],
								'payment_methods' => [
									'type'  => 'array',
									'items' => [
										'type'       => 'object',
										'properties' => [
											'id'     => [
												'type' => FieldType::STRING,
											],
											'status' => [
												'type' => FieldType::STRING,
											],
											'maximumAmount' => [
												'type' => 'object',
												'properties' => [
													'value'    => [
														'type' => FieldType::STRING,
													],
													'currency' => [
														'type' => FieldType::STRING,
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
				self::SETTING_EMAIL_RECEIPT_ENABLE    => [
					'type'              => FieldType::BOOLEAN,
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_EMAIL_BCC               => [
					'type'              => FieldType::STRING,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_email',
				],
				self::SETTING_CUSTOM_SMTP             => [
					'type'              => 'object',
					'default'           => [
						'from_email' => '',
						'from_name'  => get_bloginfo( 'name' ),
						'host'       => '',
						'port'       => '',
						'encryption' => 'tls',
						'autotls'    => false,
						'username'   => '',
						'password'   => '',
					],
					'show_in_rest'      => [
						'schema' => [
							'type'       => 'object',
							'properties' => [
								'from_email' => [
									'type' => FieldType::STRING,
								],
								'from_name'  => [
									'type' => FieldType::STRING,
								],
								'host'       => [
									'type' => FieldType::STRING,
								],
								'port'       => [
									'type' => FieldType::INTEGER,
								],
								'encryption' => [
									'type' => FieldType::STRING,
								],
								'autotls'    => [
									'type' => FieldType::BOOLEAN,
								],
								'username'   => [
									'type' => FieldType::STRING,
								],
								'password'   => [
									'type'         => FieldType::STRING,
									'show_in_rest' => false,
								],
							],
						],
					],
					'sanitize_callback' => [ self::class, 'encrypt_smtp_password' ],
				],
				self::SETTING_SMTP_PASSWORD_ENCRYPTED => [
					'type'         => FieldType::STRING,
					'show_in_rest' => false,
				],
				self::SETTING_SMTP_ENABLE             => [
					'type'              => FieldType::BOOLEAN,
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_SPAM_PROTECTION         => [
					'type'              => FieldType::BOOLEAN,
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_DEBUG_MODE              => [
					'type'              => FieldType::BOOLEAN,
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_ALWAYS_LOAD_ASSETS      => [
					'type'              => FieldType::BOOLEAN,
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				self::SETTING_INVOICE_NUMBER          => [
					'type'              => FieldType::INTEGER,
					'show_in_rest'      => true,
					'default'           => 1,
					'sanitize_callback' => 'absint',
				],
				self::SETTING_INVOICE_COMPANY_ADDRESS => [
					'type'         => FieldType::STRING,
					'show_in_rest' => true,
				],
				self::SETTING_INVOICE_VAT_NUMBER      => [
					'type'         => FieldType::STRING,
					'show_in_rest' => true,
				],
			]
		);
	}

	/**
	 * Encrypts the smtp password before storing to the database.
	 *
	 * @throws Exception Thrown when problem encrypting password.
	 *
	 * @param array $setting The smtp settings array.
	 */
	public static function encrypt_smtp_password( array $setting ): array {
		$raw_password = $setting['password'] ?? null;
		if ( $raw_password ) {
			$setting['password'] = str_repeat( '*', \strlen( $raw_password ) );
			$encrypted_password  = Auth::encrypt_password( $raw_password );
			update_option( self::SETTING_SMTP_PASSWORD_ENCRYPTED, $encrypted_password );
		} else {
			$setting['password'] = get_option( self::SETTING_CUSTOM_SMTP )['password'] ?? null;
		}
		return $setting;
	}
}
