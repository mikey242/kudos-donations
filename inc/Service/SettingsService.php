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

use IseardMedia\Kudos\Infrastructure\PluginUninstallAware;

class SettingsService extends AbstractService implements PluginUninstallAware {

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
	 * @param string     $key     Setting key.
	 * @param bool|mixed $default Optional. Default value to return if the option does not exist.
	 * @return string|array<int|string,mixed>|bool|int Setting value.
	 */
	public function get_setting( string $key, bool $default = false ): string|array|bool|int {
		// Distinguish between `false` as a default, and not passing one, just like WordPress.
		$passed_default = \func_num_args() > 1;

		if ( $passed_default ) {
			/**
			 * Setting value.
			 *
			 * @var string|array<int|string,mixed>|bool $default
			 */
			$option = get_option( $key, $default );
			if ( $option === $default ) {
				return $option;
			}
		} else {
			/**
			 * Setting value.
			 *
			 * @var string|array<int|string,mixed>|bool $key
			 */
			$option = get_option( $key );
		}

		$settings = $this->get_registered_options();
		if ( isset( $settings[ $key ] ) ) {
			$value = rest_sanitize_value_from_schema( $option, $settings[ $key ] );
			if ( is_wp_error( $value ) ) {
				return $option;
			}
			/**
			 * Setting value.
			 *
			 * @var string|array<int|string,mixed>|bool $option
			 */
			$option = $value;
		}

		return $option;
	}

	public function get_current_vendor_settings(): string|int|bool|array {
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
	public function update_setting( string $key, mixed $value ): bool {
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
		return [
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
				'type'              => 'object',
				'default'           => [
					'connected'       => false,
					'recurring'       => false,
					'mode'            => 'test',
					'payment_methods' => [],
					'test_key'        => '',
					'live_key'        => '',
				],
				'show_in_rest'      => [
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
										'id'            => [
											'type' => 'string',
										],
										'status'        => [
											'type' => 'string',
										],
										'maximumAmount' => [
											'type'       => 'object',
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
				'sanitize_callback' => [ self::class, 'sanitize_vendor' ],
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
		];
	}

	/**
	 * Sanitize vendor settings.
	 *
	 * @param array $settings Settings array.
	 */
	public static function sanitize_vendor( array $settings ): array {
		foreach ( $settings as $setting => &$value ) {
			switch ( $setting ) {
				case 'connected':
				case 'recurring':
					$value = rest_sanitize_boolean( $value );
					break;
				case 'live_key':
				case 'test_key':
				case 'mode':
					$value = sanitize_text_field( $value );
					break;
				case 'payment_methods':
					$value = self::recursive_sanitize_text_field( $value );
					break;
			}
		}

		return $settings;
	}

	/**
	 * Method to recursively sanitize all text fields in an array.
	 *
	 * @link https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
	 *
	 * @param array $array Array of values to sanitize.
	 */
	public static function recursive_sanitize_text_field( array $array ): array {
		foreach ( $array as &$value ) {
			if ( \is_array( $value ) ) {
				$value = self::recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
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
	 * {@inheritDoc}
	 */
	public function on_plugin_uninstall(): void {
		delete_option( self::SETTING_NAME_SHOW_INTRO );
		delete_option( self::SETTING_NAME_VENDOR );
		delete_option( self::SETTING_NAME_VENDOR_MOLLIE );
		delete_option( self::SETTING_NAME_EMAIL_RECEIPT_ENABLE );
		delete_option( self::SETTING_NAME_EMAIL_BCC );
		delete_option( self::SETTING_NAME_CUSTOM_SMTP );
		delete_option( self::SETTING_NAME_SMTP_ENABLE );
		delete_option( self::SETTING_NAME_SPAM_PROTECTION );
		delete_option( self::SETTING_NAME_DEBUG_MODE );
		delete_option( self::SETTING_NAME_ALWAYS_LOAD_ASSETS );
		delete_option( self::SETTING_NAME_DB_VERSION );
	}
}
