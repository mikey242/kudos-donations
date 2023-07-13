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
	 * Logger interface.
	 *
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
		$container_builder = new ContainerBuilder();

		// Enable cache if not in development mode.
		if ( 'development' !== $_ENV['APP_ENV'] ) {
			$kudos_uploads = wp_upload_dir()['basedir'] . '/kudos-donations/container';
			$container_builder->enableCompilation( $kudos_uploads );
			$container_builder->writeProxiesToFile( true, $kudos_uploads . '/proxies' );
		}

		$config_path = KUDOS_PLUGIN_DIR . '/config/';
		$container_builder->addDefinitions( $config_path . '/config.php' );

		$this->container = $container_builder->build();
		$this->logger    = $this->container->get( LoggerInterface::class );
	}

	/**
	 * Instantiate the services.
	 */
	private function instantiate_services(): void {
		try {
			// Get definitions from container.
			$definitions = $this->container->getKnownEntryNames();

			// Loop through definitions and find registrable classes.
			foreach ( $definitions as $definition ) {
				if ( ! is_a( $definition, Registrable::class, true ) ) {
					continue;
				}

				/**
				 * Run register method using specified action(s) and priority.
				 *
				 * @var Registrable $service
				 */
				$registrable = $this->container->get( $definition );

				// Skip if service not enabled.
				if ( ! $registrable->is_enabled() ) {
					continue;
				}

				// Add specified action or call register directly.
				if ( is_a( $definition, Delayed::class, true ) ) {
					foreach ( $registrable::get_registration_actions() as $action ) {
						add_action(
							$action,
							[ $registrable, 'register' ],
							$registrable::get_registration_action_priority()
						);
					}
				} else {
					$definition->register();
				}
			}
		} catch ( Exception $e ) {
			$this->logger->error( esc_html( $e->getMessage() ) );
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
}
