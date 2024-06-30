<?php
/**
 * PHP-DI container config.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

use Dompdf\Dompdf;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\VendorFactory;
use IseardMedia\Kudos\Vendor\VendorInterface;
use Mollie\Api\MollieApiClient;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function ( ContainerConfigurator $container ) {

	// Config defaults.
	$services = $container->services()
		->defaults()
			->autowire()
			->public();

	// Logger.
	$services
		->set( RotatingFileHandler::class )->args(
			[
				KUDOS_STORAGE_DIR . 'logs/' . $_ENV['APP_ENV'] . '.log',
				5,
				( 'development' === $_ENV['APP_ENV'] || KUDOS_DEBUG ) ? Logger::DEBUG : Logger::INFO,
				true,
				null,
				false,
				KUDOS_LOG_DATE_FORMAT,
			]
		)
		->set( LoggerInterface::class, Logger::class )
		->args( [ 'kudos_donations' ] )
		->call( 'pushHandler', [ service( RotatingFileHandler::class ) ] );

	// Vendor.
	$services->set( VendorInterface::class )
			->factory( [ service( VendorFactory::class ), 'create' ] )
			->args(
				[
					service( 'service_container' ),
					service( SettingsService::class ),
				]
			);

	// External libraries.
	$services
		->set( Dompdf::class )
		->set( MollieApiClient::class );

	// Load resources with exclusions.
	$services->load( 'IseardMedia\Kudos\\', '../includes/*' )
	->exclude( '../includes/{namespace.php,functions.php,helpers.php,index.php}' );
};