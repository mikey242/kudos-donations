<?php
/**
 * Main Plugin class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Infrastructure\Container\AbstractService;

class Settings extends AbstractService {

	public const PREFIX = '_kudos_';

	public function register(): void {
		$this->register_all();
	}

	public static function get_registration_actions(): array {
		return ['admin_init', 'rest_api_init', 'init'];
	}

	public static function get_registration_action_priority(): int {
		return 5;
	}

	public function register_all(): void {
		foreach ($this->get_settings() as $name => $args) {
			register_setting(
				'kudos_donations',
				self::PREFIX . $name,
				$args
			);
		}
	}

	/**
	 * Returns all settings in array.
	 *
	 * @return array
	 */
	private function get_settings(): array
	{
		return
			[
				'show_intro'             => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'migrations_pending'     => [
					'type'    => 'array',
					'default' => [],
				],
				'migration_history'      => [
					'type' => 'array',
				],
				'vendor'                 => [
					'type'         => 'string',
					'show_in_rest' => true,
					'default'      => 'mollie',
				],
				'vendor_mollie'          => [
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
					'sanitize_callback' => [$this, 'sanitize_vendor'],
				],
				'email_receipt_enable'   => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'email_bcc'              => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_email',
				],
				'custom_smtp'            => [
					'type'         => 'object',
					'default'      => [
						'from_email' => '',
						'from_name'  => get_bloginfo('name'),
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
				'smtp_enable'            => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'spam_protection'        => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'return_message_enable'  => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => true,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'custom_return_enable'   => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'custom_return_url'      => [
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'esc_url_raw',
				],
				'debug_mode'             => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'always_load_assets'     => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'donate_modal_in_footer' => [
					'type'              => 'boolean',
					'show_in_rest'      => true,
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				],
				'disable_object_cache'   => [
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
	 * @param $settings
	 *
	 * @return mixed
	 */
	public static function sanitize_vendor($settings): mixed {
		foreach ($settings as $setting => &$value) {
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
	 * Method to recursively sanitize all text fields in an array.
	 *
	 * @param array $array Array of values to sanitize.
	 *
	 * @return mixed
	 * @source https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
	 */
	public static function recursive_sanitize_text_field(array $array): array
	{
		foreach ($array as &$value) {
			if (is_array($value)) {
				$value = self::recursive_sanitize_text_field($value);
			} else {
				$value = sanitize_text_field($value);
			}
		}

		return $array;
	}
}