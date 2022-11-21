<?php

use IseardMedia\Kudos\Controller\Admin;
use IseardMedia\Kudos\Controller\Front;
use IseardMedia\Kudos\Controller\Rest\RestRoutes;
use IseardMedia\Kudos\Service\ActivatorService;
use IseardMedia\Kudos\Service\PaymentService;

use function DI\autowire;

return [
    'ActivatorService' => autowire(ActivatorService::class),
    'Front'            => autowire(Front::class)
        ->constructor(KUDOS_VERSION),
    'Admin'            => autowire(Admin::class)
        ->constructor(KUDOS_VERSION),
    'RestRoutes'       => autowire(RestRoutes::class),
    'PaymentService'   => autowire(PaymentService::class),
];
