<?php
/**
 * PHP-DI container config.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

use IseardMedia\Kudos\Admin\BaseAdminPage;
use IseardMedia\Kudos\Admin\CampaignAdminPage;
use IseardMedia\Kudos\Admin\DonorAdminPage;
use IseardMedia\Kudos\Admin\SettingsAdminPage;
use IseardMedia\Kudos\Admin\SubscriptionsAdminPage;
use IseardMedia\Kudos\Admin\TransactionsAdminPage;
use IseardMedia\Kudos\Controller\Admin;
use IseardMedia\Kudos\Controller\Front;
use IseardMedia\Kudos\Controller\Rest\Mail;
use IseardMedia\Kudos\Controller\Rest\Payment;
use IseardMedia\Kudos\Controller\Rest\Transaction;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Service\Settings;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
	Admin::class                   => DI\autowire(),
	Front::class                   => DI\autowire(),
	BaseAdminPage::class           => DI\autowire(),
	CampaignAdminPage::class       => DI\autowire(),
	SettingsAdminPage::class       => DI\autowire(),
	TransactionsAdminPage::class   => DI\autowire(),
	DonorAdminPage::class          => DI\autowire(),
	SubscriptionsAdminPage::class  => DI\autowire(),
	CampaignPostType::class        => DI\autowire(),
	DonorPostType::class           => DI\autowire(),
	SubscriptionPostType::class    => DI\autowire(),
	TransactionPostType::class     => DI\autowire(),
	Transaction::class             => DI\autowire(),
	Mail::class                    => DI\autowire(),
	Payment::class                 => DI\autowire(),
	Settings::class                => DI\autowire(),
	Psr\Log\LoggerInterface::class => DI\factory(
		function () {
			$logger       = new Logger( 'kudos_log' );
			$file_handler = new StreamHandler( wp_upload_dir()['basedir'] . '/kudos-donations/logs/' . $_ENV['APP_ENV'] . '.log', Logger::DEBUG );
			$file_handler->setFormatter( new LineFormatter() );
			$logger->pushHandler( $file_handler );
			return $logger;
		}
	),
];
