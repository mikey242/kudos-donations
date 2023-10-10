<?php
/**
 * Plugin Activator service.
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

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
