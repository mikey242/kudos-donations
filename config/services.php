<?php
/**
 * Symfony DI container config.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

use Dompdf\Dompdf;
use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Container\Registrable;
use IseardMedia\Kudos\Container\UpgradeAwareInterface;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\VendorFactory;
use IseardMedia\Kudos\Vendor\VendorInterface;
use Mollie\Api\MollieApiClient;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function ( ContainerConfigurator $container ) {

	// Config defaults.
	$services = $container->services()
		->defaults()
			->autowire()
			->public();

	// Define the rotating file handler.
	$services
		->set( RotatingFileHandler::class )
		->args(
			[
				KUDOS_STORAGE_DIR . 'logs/' . KUDOS_APP_ENV . '.log',
				5,
				( KUDOS_ENV_IS_DEVELOPMENT || KUDOS_DEBUG ) ? Logger::DEBUG : Logger::INFO,
				true,
				null,
				false,
			]
		);

	// Define the WhatFailureGroupHandler to wrap around the RotatingFileHandler.
	$services
		->set( WhatFailureGroupHandler::class )
		->args(
			[
				[ service( RotatingFileHandler::class ) ],
			]
		);

	// Logger with WhatFailureGroupHandler to suppress exceptions.
	$services
		->set( LoggerInterface::class, Logger::class )
		->args( [ 'kudos_donations' ] )
		->call( 'pushHandler', [ service( WhatFailureGroupHandler::class ) ] );

	// Set logger on required services.
	$services->instanceof( LoggerAwareInterface::class )
		->call( 'setLogger', [ service( LoggerInterface::class ) ] );

	// Tag services.
	$services->instanceof( Registrable::class )
		->tag( 'kudos.registrable' );
	$services->instanceof( ActivationAwareInterface::class )
		->tag( 'kudos.activation' );
	$services->instanceof( UpgradeAwareInterface::class )
		->tag( 'kudos.upgradeable' );
	$services->instanceof( HasSettingsInterface::class )
		->tag( 'kudos.has_settings' );
	$services->instanceof( MigrationInterface::class )
		->tag( 'kudos.migration' );

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
	$services->load( 'IseardMedia\Kudos\\', KUDOS_PLUGIN_DIR . 'includes/*' )
		->exclude( KUDOS_PLUGIN_DIR . 'includes/{namespace.php,functions.php,helpers.php,index.php}' );

	// Filter for adding additional services.
	apply_filters( 'kudos_container_configurator', $services );
};
