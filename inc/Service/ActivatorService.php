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

	/**
	 * Activator service constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 * @param TwigService     $twig Twig service instance.
	 */
	public function __construct( private LoggerInterface $logger, private TwigService $twig ) {}

	/**
	 * Runs all activation functions.
	 */
	public function activate(): void {

		$logger = $this->logger;
		$twig   = $this->twig;
		$twig->init();
		$logger->info( 'Kudos Donations plugin activated.', [ 'version' => KUDOS_VERSION ] );
	}
}
