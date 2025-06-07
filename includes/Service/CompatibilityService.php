<?php
/**
 * Compatibility service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use WP_Error;

class CompatibilityService {

	private const MINIMUM_PHP_VERSION = '7.4';
	private const MINIMUM_WP_VERSION  = '6.6';

	/**
	 * Runs the compatibility check on plugin activation.
	 */
	public function on_plugin_activation(): void {
		$compatibility = $this->check_compatibility();
		if ( is_wp_error( $compatibility ) ) {
			wp_die(
				\sprintf(
				/* translators: %s: Error message returned by check */
					esc_html( __( 'Kudos Donations failed to activate. Error: %s', 'kudos-donations' ) ),
					esc_html( $compatibility->get_error_message() )
				),
				esc_html( __( 'Kudos Donations Activation Error', 'kudos-donations' ) ),
				[ 'back_link' => true ]
			);
		}
	}

	/**
	 * Checks PHP version compatibility.
	 *
	 * @return true|WP_Error True if compatible, WP_Error if not.
	 */
	private function check_php_compatibility() {
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			return new WP_Error(
				'php_version_error',
				\sprintf(
				/* translators: 1: Current PHP version 2: Required PHP version */
					__( 'Kudos Donations requires PHP version %2$s or higher. Your current version is %1$s.', 'kudos-donations' ),
					PHP_VERSION,
					self::MINIMUM_PHP_VERSION
				)
			);
		}

		return true;
	}

	/**
	 * Checks WordPress version compatibility.
	 *
	 * @return true|WP_Error True if compatible, WP_Error if not.
	 */
	private function check_wp_compatibility() {
		global $wp_version;
		if ( version_compare( $wp_version, self::MINIMUM_WP_VERSION, '<' ) ) {
			return new WP_Error(
				'wp_version_error',
				\sprintf(
				/* translators: 1: Current WordPress version 2: Required WordPress version */
					__( 'Kudos Donations requires WordPress version %2$s or higher. Your current version is %1$s.', 'kudos-donations' ),
					$wp_version,
					self::MINIMUM_WP_VERSION
				)
			);
		}

		return true;
	}

	/**
	 * Checks all compatibility requirements.
	 *
	 * @return true|WP_Error True if all checks pass, WP_Error if any fail.
	 */
	public function check_compatibility() {
		$php_compatibility = $this->check_php_compatibility();
		if ( is_wp_error( $php_compatibility ) ) {
			return $php_compatibility;
		}

		$wp_compatibility = $this->check_wp_compatibility();
		if ( is_wp_error( $wp_compatibility ) ) {
			return $wp_compatibility;
		}

		return true;
	}
}
