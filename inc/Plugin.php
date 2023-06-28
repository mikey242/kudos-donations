<?php
/**
 * Main Plugin class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use IseardMedia\Kudos\Infrastructure\Container\AbstractService;
use IseardMedia\Kudos\Infrastructure\Container\Delayed;
use IseardMedia\Kudos\Infrastructure\Container\Registrable;
use Psr\Log\LoggerInterface;
use function add_action;
use function load_plugin_textdomain;

/**
 * Class Plugin.
 */
class Plugin {

	/**
	 * Symfony's container builder.
	 *
	 * @var Container
	 */
	private Container $container;
	/**
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Initialize the services.
	 */
	public function on_plugin_loaded(): void {
		$this->instantiate_services();
		$this->setup_localization();
	}

	/**
	 * Runs on plugin activation.
	 *
	 * @param bool $network_wide Whether activation is network-wide or not.
	 */
	public function on_plugin_activation( bool $network_wide ): void {
		// TODO implement on_plugin_activation method.
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @param bool $network_wide Whether activation is network-wide or not.
	 */
	public function on_plugin_deactivation( bool $network_wide ): void {
		// TODO implement on_plugin_activation method.
	}

	/**
	 * Loading our plugin's text domain and letting WordPress know where to find its translations.
	 */
	protected function setup_localization(): void {
		add_action(
			'init',
			static function (): void {
				load_plugin_textdomain( 'kudos-donations', false, 'kudos-donations/languages' );
			}
		);
	}

	/**
	 * Create and configure the container.
	 *
	 * @throws Exception Handled by ::register.
	 */
	private function build_container(): void {
		$containerBuilder = new ContainerBuilder();

		$config_path = KUDOS_PLUGIN_DIR . '/config/';
		$containerBuilder->addDefinitions($config_path . '/config.php');

		$this->container = $containerBuilder->build();
		$this->logger = $this->container->get(LoggerInterface::class);
	}

	/**
	 * Instantiate the services.
	 */
	private function instantiate_services(): void {
		try {
			// Get definitions from container.
			$definitions = $this->container->getKnownEntryNames();

			// Loop through definitions and find registrable classes.
			foreach ($definitions as $definition) {
				if(! is_a($definition, Registrable::class, true)) {
					continue;
				}
				/**
				 * Run register method using specified action(s) and priority.
				 * @var AbstractService $service
				 */
				$service = $this->container->get($definition);

				// Bail if service not enabled.
				if(! $service->is_enabled()) {
					continue;
				}

				// Add specified action or call register directly.
				if(is_a($definition, Delayed::class, true)) {
					foreach ($service::get_registration_actions() as $action) {
						add_action(
							$action,
							[ $service, 'register' ],
							$service::get_registration_action_priority()
						);
					}
				} else {
					$definition->register();
				}
			}

		} catch ( Exception $e ) {
			$this->logger->error(esc_html( $e->getMessage() ));
		}
	}

	/**
	 * Register the plugin.
	 */
	public function register(): void {
		try {
			$this->build_container();
			$this->on_plugin_loaded();
			do_action( 'kudos_donations_loaded', $this->container );
		} catch ( \Throwable $e ) {
			// TODO: add logging.
			wp_die( esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Get the specified entry from the container.
	 *
	 * @throws Exception No entry was found in the container.
	 *
	 * @param string $service_id Id of service to get.
	 */
	protected function get_service( string $service_id ): ?object {
		return $this->container->get( $service_id );
	}
}
