<?php
/**
 * Autoloader for Kudos Donations.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @source https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/woocommerce.php
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos;

class Autoloader {

	/**
	 * Static-only class.
	 */
	private function __construct() {}

	/**
	 * Require autoloaders and return the result.
	 *
	 * If an autoloader is not present, let's log the failure and display a nice admin notice.
	 */
	public static function init(): bool {
		$autoloaders = [
			\dirname( __DIR__ ) . '/vendor/autoload_packages.php',
			\dirname( __DIR__ ) . '/third-party/vendor/autoload.php',
		];

		// Load standard Composer autoloader.
		foreach ( $autoloaders as $autoloader ) {
			if ( ! is_readable( $autoloader ) ) {
				self::missing_autoloader();
				return false;
			}

			/** @psalm-suppress UnresolvableInclude */
			require $autoloader;
		}

		return true;
	}

	/**
	 * If the autoloader is missing, add an admin notice.
	 */
	protected static function missing_autoloader(): void {
		if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// This message is not translated as at this point it's too early to load translations.
			error_log(  // phpcs:ignore
				esc_html( 'Your installation of Kudos Donations is incomplete.' )
			);
		}
		add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-error">
					<p>
						<?php
							esc_html_e( 'Your installation of Kudos Donations is incomplete.', 'kudos-donations' )
						?>
					</p>
				</div>
				<?php
			}
		);
	}
}
