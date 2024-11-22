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
use IseardMedia\Kudos\Container\EncryptionAwareInterface;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Container\Registrable;
use IseardMedia\Kudos\Container\UpgradeAwareInterface;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorFactory;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorInterface;
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

	// Set encryption service on required services.
	$services->instanceof( EncryptionAwareInterface::class )
		->call( 'set_encryption', [ service( EncryptionService::class ) ] );

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
	$services->set( PaymentVendorInterface::class )
		->factory( [ service( PaymentVendorFactory::class ), 'create' ] )
		->args(
			[
				service( 'service_container' ),
			]
		);

	// External libraries.
	$services
		->set( Dompdf::class )
		->set( MollieApiClient::class );

	// Load base plugin.
	$services->load( 'IseardMedia\Kudos\\', KUDOS_PLUGIN_DIR . 'common/includes/*' )
		->exclude( KUDOS_PLUGIN_DIR . 'common/includes/{namespace.php,functions.php,helpers.php,index.php}' );

	// Load premium plugin.
	if ( \IseardMedia\Kudos\kd_fs()->is__premium_only() ) {
		// Newsletter providers.
		$services->set( \IseardMedia\Kudos\Vendor\VendorInterface::class )
			->factory( [ service( IseardMedia\KudosPremium\NewsletterProvider\NewsletterProviderFactory::class ), 'create' ] )
			->args(
				[
					service( 'service_container' ),
				]
			);
		$services->load( 'IseardMedia\KudosPremium\\', KUDOS_PLUGIN_DIR . 'premium/includes/*' )
			->exclude( KUDOS_PLUGIN_DIR . 'premium/includes/{namespace.php,functions.php,helpers.php,index.php}' );
		$services
			->set( MailchimpMarketing\ApiClient::class )
			->set( MailerLite\MailerLite::class );
	}

	// Filter for adding additional services.
	apply_filters( 'kudos_container_configurator', $services );
};
