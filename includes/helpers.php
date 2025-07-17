<?php
/**
 * Helper functions.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

if ( ! function_exists( 'dd' ) ) {
	/**
	 * Dump provided parameter and stop.
	 *
	 * @param mixed $data Variable to dump.
	 *
	 *  phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
	 *  phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	function dd( $data ): void {
		die( print( '<pre>' . print_r( $data, true ) . '</pre>' ) );
	}
}
