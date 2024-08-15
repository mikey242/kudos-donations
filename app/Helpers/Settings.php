<?php
/**
 * Settings helper.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Helpers;

class Settings {

	private const PREFIX = '_kudos_';

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
	 * Gets the settings for the current vendor.
	 *
	 * @return mixed
	 */
	public static function get_current_vendor_settings() {

		return self::get_setting( 'vendor_' . self::get_setting( 'payment_vendor' ) );
	}

	/**
	 * Returns setting value.
	 *
	 * @param string $name Setting name without prefix.
	 * @param mixed  $default_value Default value to use if none found.
	 * @return mixed
	 */
	public static function get_setting( string $name, $default_value = false ) {

		return get_option( self::PREFIX . $name, $default_value );
	}

	/**
	 * Update specified setting.
	 *
	 * @param string $name Setting name without prefix.
	 * @param mixed  $value Setting value.
	 */
	public static function update_setting( string $name, $value ): bool {

		return update_option( self::PREFIX . $name, $value );
	}

	/**
	 * Updates specific values in serialized settings array.
	 * e.g. update_array('my_setting', ['enabled' => false]).
	 *
	 * @param string $name Setting array name without prefix.
	 * @param array  $value Array of name=>values in setting to update.
	 */
	public static function update_array( string $name, array $value ): bool {

		// Grab current data.
		$current = self::get_setting( $name );

		// Check if setting is either an array or null.
		if ( \is_array( $current ) || ! null ) {
			// Merge provided data and current data then update setting.
			$new = wp_parse_args( $value, $current );

			return self::update_setting( $name, $new );
		}

		return false;
	}

	/**
	 * Register all the settings.
	 *
	 * @param array $settings Settings array.
	 */
	public static function register_settings( array $settings = [] ) {

		foreach ( $settings as $name => $setting ) {
			register_setting(
				'kudos_donations',
				self::PREFIX . $name,
				$setting
			);
		}
	}

	/**
	 * Add the settings to the database.
	 *
	 * @param array $settings Settings array.
	 */
	public static function add_defaults( array $settings = [] ) {

		foreach ( $settings as $name => $setting ) {
			if ( isset( $setting['default'] ) ) {
				add_option( self::PREFIX . $name, $setting['default'] );
			}
		}
	}

	/**
	 * Removes all settings from database.
	 *
	 * @param array $settings Settings array.
	 */
	public static function remove_settings( array $settings = [] ) {

		foreach ( $settings as $key => $setting ) {
			self::remove_setting( $key );
		}
	}

	/**
	 * Remove specified setting from database.
	 *
	 * @param string $name Setting name.
	 */
	public static function remove_setting( string $name ): bool {

		return delete_option( self::PREFIX . $name );
	}

	/**
	 * Method to recursively sanitize all text fields in an array.
	 *
	 * @param array $args Array of values to sanitize.
	 * @return mixed
	 *
	 * @source https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
	 */
	public static function recursive_sanitize_text_field( array $args ): array {

		foreach ( $args as &$value ) {
			if ( \is_array( $value ) ) {
				$value = self::recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $args;
	}
}
