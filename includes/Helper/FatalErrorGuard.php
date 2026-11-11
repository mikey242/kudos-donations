<?php
/**
 * Stops Kudos from starting after it broke its own startup.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Helper;

/**
 * A fatal error while Kudos starts up would happen again on every request, taking the
 * whole site down with it. Such an error is recorded, and Kudos then stays switched off
 * (the rest of the site keeps working) until it is reactivated or updated.
 *
 * Only errors during startup count: up to WordPress's wp_loaded, which follows init,
 * where Kudos registers its services. A fatal error later in a request, e.g. in a REST
 * call, a webhook or PDF generation, affects that request only. Switching Kudos off for
 * it would take every donation form offline for good; WordPress's own fatal error
 * protection already deals with those.
 */
final class FatalErrorGuard {

	public const OPTION = '_kudos_fatal_error';

	private const FATAL_TYPES = [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ];

	private static bool $starting_up = true;

	/**
	 * Starts watching this request. Call as early as possible in the main plugin file.
	 */
	public static function register(): void {
		register_shutdown_function( [ self::class, 'on_shutdown' ] );
		add_action( 'wp_loaded', [ self::class, 'on_startup_complete' ], PHP_INT_MAX );
	}

	/**
	 * Startup has finished; later errors are not recorded.
	 */
	public static function on_startup_complete(): void {
		self::$starting_up = false;
	}

	/**
	 * Shutdown handler: records the request's fatal error if it qualifies.
	 */
	public static function on_shutdown(): void {
		self::record( error_get_last(), self::$starting_up );
	}

	/**
	 * Records a fatal error from Kudos's own files that happened during startup.
	 *
	 * @param array|null $error         An error_get_last() result.
	 * @param bool       $during_startup Whether the request was still starting up.
	 */
	public static function record( ?array $error, bool $during_startup ): void {
		if (
			! $during_startup
			|| ! $error
			|| ! \in_array( $error['type'] ?? null, self::FATAL_TYPES, true )
			|| 0 !== strpos( (string) ( $error['file'] ?? '' ), KUDOS_PLUGIN_DIR )
		) {
			return;
		}

		update_option(
			self::OPTION,
			[
				'message' => $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
				'version' => KUDOS_VERSION,
			],
			false
		);
	}

	/**
	 * The recorded error that keeps Kudos switched off, or null to start normally.
	 *
	 * An error recorded by another version (or before versions were stored) is cleared:
	 * the update may have fixed it, and if not it is simply recorded again.
	 */
	public static function get_blocking_error(): ?string {
		$stored = get_option( self::OPTION );

		if ( ! $stored ) {
			return null;
		}

		if ( ! \is_array( $stored ) || ( $stored['version'] ?? null ) !== KUDOS_VERSION ) {
			self::clear();
			return null;
		}

		return (string) ( $stored['message'] ?? '' );
	}

	/**
	 * Forgets the recorded error, e.g. on (re)activation.
	 */
	public static function clear(): void {
		delete_option( self::OPTION );
	}
}
