<?php
/**
 * Helper functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

/**
 * Dump provided parameter and stop.
 *
 * @param mixed $data Variable to dump.
 *  phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
 *  phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */
function dd( $data ): void {
	die( print( '<pre>' . print_r( $data, true ) . '</pre>' ) );
}
