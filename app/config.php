<?php

use Kudos\Controller\Admin;
use Kudos\Controller\Front;
use Kudos\Controller\Rest\RestRoutes;
use Kudos\Service\ActivatorService;
use Kudos\Service\PaymentService;
use function DI\autowire;

return [
	'ActivatorService' => autowire( ActivatorService::class ),
	'Front'            => autowire( Front::class )
		->constructor( KUDOS_VERSION ),
	'Admin'            => autowire( Admin::class )
		->constructor( KUDOS_VERSION ),
	'RestRoutes' => autowire( RestRoutes::class ),
	'PaymentService'   => autowire( PaymentService::class ),
];