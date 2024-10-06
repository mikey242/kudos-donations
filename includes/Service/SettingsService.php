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

use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;

class SettingsService implements HasSettingsInterface {
	public const SETTING_SHOW_INTRO       = '_kudos_show_intro';
	public const SETTING_DEBUG_MODE       = '_kudos_debug_mode';
	public const SETTING_MAXIMUM_DONATION = '_kudos_maximum_donation';
	public const SETTING_ALLOW_METRICS    = '_kudos_allow_metrics';

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
	public function get_settings(): array {
		return [
			self::SETTING_SHOW_INTRO       => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::SETTING_DEBUG_MODE       => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::SETTING_MAXIMUM_DONATION => [
				'type'         => FieldType::INTEGER,
				'show_in_rest' => true,
				'default'      => 5000,
			],
			self::SETTING_ALLOW_METRICS    => [
				'type'         => FieldType::BOOLEAN,
				'show_in_rest' => true,
				'default'      => false,
			],
		];
	}
}
