<?php

use IseardMedia\Kudos\Admin\BaseAdminPage;
use IseardMedia\Kudos\Admin\CampaignAdminPage;
use IseardMedia\Kudos\Admin\SettingsAdminPage;
use IseardMedia\Kudos\Controller\Admin;
use IseardMedia\Kudos\Controller\Front;
use IseardMedia\Kudos\Controller\Rest\Mail;
use IseardMedia\Kudos\Controller\Rest\Payment;
use IseardMedia\Kudos\Controller\Rest\Transaction;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
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
	CampaignPostType::class        => DI\autowire(),
	DonorPostType::class           => DI\autowire(),
	TransactionPostType::class           => DI\autowire(),
	Transaction::class             => DI\autowire(),
	Mail::class                    => DI\autowire(),
	Payment::class                 => DI\autowire(),
	Settings::class                => DI\autowire(),
	Psr\Log\LoggerInterface::class => DI\factory(function () {
		$logger = new Logger('mylog');
		$fileHandler = new StreamHandler(wp_upload_dir()['basedir'] . '/kudos-donations/logs/test.log', Logger::DEBUG);
		$fileHandler->setFormatter(new LineFormatter());
		$logger->pushHandler($fileHandler);
		return $logger;
	}),
];
