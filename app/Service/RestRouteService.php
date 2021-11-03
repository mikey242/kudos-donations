<?php

namespace Kudos\Service;

use Kudos\Rest\Route\MailerRoutes;
use Kudos\Rest\Route\PaymentRoutes;
use Kudos\Rest\Route\TransactionRoutes;

class RestRouteService {

	/**
	 * Namespace used for registering the routes.
	 */
	const NAMESPACE = 'kudos/v1';

	/**
	 * @var array[]
	 */
	private $routes;

	/**
	 * RestRoutesService constructor.
	 */
	public function __construct(
		PaymentRoutes $payment_routes,
		MailerRoutes $mailer_routes,
		TransactionRoutes $transaction_routes
	) {

		$this->routes[] = $payment_routes->get_routes();
		$this->routes[] = $mailer_routes->get_routes();
		$this->routes[] = $transaction_routes->get_routes();

	}

	/**
	 * Called to register all the routes defined in this service.
	 */
	public function register_all() {

		foreach ( $this->routes as $service ) {
			foreach ( $service as $route => $args ) {
				register_rest_route( self::NAMESPACE, $route, $args );
			}
		}

	}
}