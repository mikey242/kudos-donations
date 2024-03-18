<?php
/**
 * PHP-DI container config.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

use IseardMedia\Kudos\Admin\CampaignAdminPage;
use IseardMedia\Kudos\Admin\DonorAdminPage;
use IseardMedia\Kudos\Admin\ParentAdminPage;
use IseardMedia\Kudos\Admin\SettingsAdminPage;
use IseardMedia\Kudos\Admin\SubscriptionsAdminPage;
use IseardMedia\Kudos\Admin\TransactionsAdminPage;
use IseardMedia\Kudos\Controller\Admin;
use IseardMedia\Kudos\Controller\Front;
use IseardMedia\Kudos\Controller\Rest\Mail;
use IseardMedia\Kudos\Controller\Rest\Payment;
use IseardMedia\Kudos\Controller\Rest\Subscription;
use IseardMedia\Kudos\Controller\Rest\Transaction;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Plugin;
use IseardMedia\Kudos\Service\MailerService;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\MollieVendor;
use IseardMedia\Kudos\Vendor\PaymentVendors;
use IseardMedia\Kudos\Vendor\VendorInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\factory;

return [
	Plugin::class                 => autowire(),
	MollieVendor::class           => autowire(),
	Admin::class                  => autowire(),
	Front::class                  => autowire(),
	CampaignAdminPage::class      => autowire(),
	ParentAdminPage::class        => autowire(),
	SettingsAdminPage::class      => autowire(),
	TransactionsAdminPage::class  => autowire(),
	DonorAdminPage::class         => autowire(),
	SubscriptionsAdminPage::class => autowire(),
	CampaignPostType::class       => autowire(),
	DonorPostType::class          => autowire(),
	SubscriptionPostType::class   => autowire(),
	TransactionPostType::class    => autowire(),
	Transaction::class            => autowire(),
	Mail::class                   => autowire(),
	Payment::class                => autowire(),
	Subscription::class           => autowire(),
	SettingsService::class        => autowire(),
	PaymentService::class         => autowire(),
	MailerService::class          => autowire(),
	VendorInterface::class        => factory(
		function ( ContainerInterface $container, SettingsService $settings, PaymentVendors $payment_vendors ) {
			$vendor_class = $payment_vendors->get_current_vendor_class();
			if ( $vendor_class ) {
				return $container->get( $vendor_class );
			}
			return null;
		}
	),
	LoggerInterface::class        => factory(
		function () {
			$app_env      = $_ENV['APP_ENV'];
			$logger       = new Logger( 'kudos_donations' );
			$file_handler = new StreamHandler( KUDOS_STORAGE_DIR . 'logs/' . $app_env . '.log', ( 'development' === $app_env || KUDOS_DEBUG ) ? Logger::DEBUG : Logger::INFO );
			$file_handler->setFormatter( new LineFormatter( null, null, false, true ) );
			$logger->pushHandler( $file_handler );
			return $logger;
		}
	),
];
