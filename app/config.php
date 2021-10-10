<?php

use Kudos\Controller\Admin;
use Kudos\Controller\Front;
use Kudos\Service\ActivatorService;
use Kudos\Service\PaymentService;
use Kudos\Service\RestRouteService;
use function DI\autowire;

return [
	'ActivatorService' => autowire( ActivatorService::class ),
	'Front'            => autowire( Front::class )
		->constructor( KUDOS_VERSION ),
	'Admin'            => autowire( Admin::class )
		->constructor( KUDOS_VERSION ),
	'RestRouteService' => autowire( RestRouteService::class ),
	'PaymentService'   => autowire( PaymentService::class ),
];