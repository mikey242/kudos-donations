<?php

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Helper\Settings;
use Psr\Log\LoggerInterface;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class ActivatorService
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var TwigService
     */
    private TwigService $twig;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->twig = new TwigService($this->logger);
    }

    /**
     * Runs all activation functions.
     */
    public function activate(): void {

        $logger = $this->logger;
        $twig   = $this->twig;
        $twig->init();

        $db_version = get_option('_kudos_donations_version');

        if ($db_version) {
            self::queue_migrations($db_version);
        }

        update_option('_kudos_donations_version', KUDOS_VERSION);
        $logger->info('Kudos Donations plugin activated.', ['version' => KUDOS_VERSION]);
    }

    /**
     * Run migrations if upgrading.
     *
     * @param string $db_version
     */
    private function queue_migrations(string $db_version): void {
        if (version_compare($db_version, KUDOS_VERSION, '<')) {
            $logger = $this->logger;

            $logger->info(
                'Upgrade detected, running migrations.',
                ['old_version' => $db_version, 'new_version' => KUDOS_VERSION]
            );

            if (version_compare($db_version, '4.0.0', '<')) {
                Settings::update_array('migrations_pending', ['400']);
            }
        }
    }
}
