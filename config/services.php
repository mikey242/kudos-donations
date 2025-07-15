<?php
/**
 * Symfony DI container config.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 *
 * @phpcs:disable WordPress.WP.ClassNameCase.Incorrect
 */

declare( strict_types=1 );

use Dompdf\Dompdf;
use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Container\EncryptionAwareInterface;
use IseardMedia\Kudos\Container\Handler\ActivationHandler;
use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Container\Handler\RegistrableHandler;
use IseardMedia\Kudos\Container\Handler\SettingsHandler;
use IseardMedia\Kudos\Container\Handler\UpgradeHandler;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Container\Registrable;
use IseardMedia\Kudos\Container\UpgradeAwareInterface;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryManager;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Domain\Schema\CampaignSchema;
use IseardMedia\Kudos\Domain\Schema\DonorSchema;
use IseardMedia\Kudos\Domain\Schema\SubscriptionSchema;
use IseardMedia\Kudos\Domain\Schema\TransactionSchema;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\MollieApiClient;
use IseardMedia\Kudos\ThirdParty\Monolog\Handler\RotatingFileHandler;
use IseardMedia\Kudos\ThirdParty\Monolog\Handler\WhatFailureGroupHandler;
use IseardMedia\Kudos\ThirdParty\Monolog\Logger;
use IseardMedia\Kudos\Vendor\EmailVendor\EmailVendorFactory;
use IseardMedia\Kudos\Vendor\EmailVendor\EmailVendorInterface;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorFactory;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function ( ContainerConfigurator $container_configurator ): void {
	$services = $container_configurator->services();

	$services->defaults()
		->autowire()
		->public();

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
	$services->instanceof( PaymentVendorInterface::class )
			->tag( 'kudos.payment_vendor' );
	$services->instanceof( EmailVendorInterface::class )
			->tag( 'kudos.email_vendor' );
	$services->instanceof( RepositoryInterface::class )
			->tag( 'kudos.repository' );

	// Set encryption service on required services.
	$services->instanceof( EncryptionAwareInterface::class )
			->call( 'set_encryption', [ service( EncryptionService::class ) ] );

	// Set logger on required services.
	$services->instanceof( LoggerAwareInterface::class )
			->call( 'setLogger', [ service( LoggerInterface::class ) ] );

	// Add repository manager to repositories.
	$services->instanceof( RepositoryAwareInterface::class )
			->call( 'set_repository_manager', [ service( RepositoryManager::class ) ] );

	// Load base plugin.
	$services->load( 'IseardMedia\Kudos\\', KUDOS_PLUGIN_DIR . 'includes/*' )
			->exclude( KUDOS_PLUGIN_DIR . 'includes/{namespace.php,functions.php,helpers.php,index.php,vendor,Domain/Entity}' );

	$services->set( RotatingFileHandler::class )
		->args(
			[
				'%env(KUDOS_STORAGE_DIR)%logs/%env(APP_ENV)%.log',
				'5',
				'%env(LOG_LEVEL)%',
			]
		);

	$services->set( WhatFailureGroupHandler::class )
		->args( [ [ service( RotatingFileHandler::class ) ] ] );

	$services->set( LoggerInterface::class, Logger::class )
		->args( [ 'kudos_donations' ] )
		->call( 'pushHandler', [ service( WhatFailureGroupHandler::class ) ] );

	// External libraries.
	$services->set( Dompdf::class );
	$services->set( MollieApiClient::class );

	// Register repositories with managers.
	$services->set( PaymentVendorFactory::class )
		->args( [ tagged_locator( 'kudos.payment_vendor' ) ] );
	$services->set( SettingsHandler::class )
		->args( [ tagged_locator( 'kudos.has_settings' ), 'kudos-donations' ] );
	$services->set( EmailVendorFactory::class )
		->args( [ tagged_locator( 'kudos.email_vendor' ) ] );
	$services->set( RegistrableHandler::class )
		->args( [ tagged_iterator( 'kudos.registrable' ) ] );
	$services->set( ActivationHandler::class )
		->args( [ tagged_iterator( 'kudos.activation' ) ] );
	$services->set( UpgradeHandler::class )
		->args( [ tagged_iterator( 'kudos.upgradeable' ) ] );
	$services->set( RepositoryManager::class )
		->args( [ tagged_iterator( 'kudos.repository' ) ] );
	$services->set( MigrationHandler::class )
		->args( [ tagged_iterator( 'kudos.migration' ) ] );

	// Configure repositories.
	$services->set( TransactionRepository::class )
			->args(
				[
					service( WpDb::class ),
					service( TransactionSchema::class ),
				]
			);

	$services->set( CampaignRepository::class )
			->args(
				[
					service( WpDb::class ),
					service( CampaignSchema::class ),
				]
			);

	$services->set( DonorRepository::class )
			->args(
				[
					service( WpDb::class ),
					service( DonorSchema::class ),
				]
			);

	$services->set( SubscriptionRepository::class )
			->args(
				[
					service( WpDb::class ),
					service( SubscriptionSchema::class ),
				]
			);

	// Filter for adding additional services.
	do_action( 'kudos_container_configurator', $services );
};
