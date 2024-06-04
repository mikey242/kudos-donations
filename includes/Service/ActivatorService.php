<?php
/**
 * Plugin Activator service.
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Helper\Utils;
use Psr\Log\LoggerInterface;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class ActivatorService {
	private LoggerInterface $logger;
	private TwigService $twig;

	/**
	 * Activator service constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 * @param TwigService     $twig Twig service instance.
	 */
	public function __construct( LoggerInterface $logger, TwigService $twig ) {
		$this->twig   = $twig;
		$this->logger = $logger;
	}

	/**
	 * Runs all activation functions.
	 *
	 * @param bool $network_wide Whether the activation is network wide or not.
	 */
	public function activate( bool $network_wide ): void {
		$logger = $this->logger;
		$twig   = $this->twig;

		// Clear container cache.
		$cache_dir = wp_upload_dir()['basedir'] . '/kudos-donations/container/';
		Utils::recursively_clear_cache( $cache_dir );

		// Initialize twig.
		$twig->init();
		$logger->info(
			'Kudos Donations plugin activated.',
			[
				'version'      => KUDOS_VERSION,
				'network_wide' => $network_wide,
			]
		);
	}
}
