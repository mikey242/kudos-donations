<?php

namespace Kudos\Mollie;

use Kudos\Transactions\Transaction;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class Webhook {

	private $transaction;
	private $mollie;

	public function __construct() {
		$this->transaction = new Transaction();
		$this->mollie = new Mollie();
	}

	/**
	 * Register webhook using rest
	 * @return void
	 */
	public function register_webhook() {
		register_rest_route( 'kudos/v1', 'mollie/', [
			'methods' => 'POST',
			'callback' => [$this, 'rest_api_mollie_webhook'],
			'args' => [
				'id' => [
					'required' => true
				]
			]
		] );
	}

	/**
	 * Mollie webhook action
	 * @param WP_REST_Request $request
	 * @return mixed|WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function rest_api_mollie_webhook( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		error_log('Webhook received with ID: ' . $id);

		/**
		 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
		 */
		$response = rest_ensure_response(
			[
				'success' => true,
				'id'      => $id,
			]
		);

		$response->add_link( 'self', rest_url( $request->get_route() ) );

		$payment = $this->mollie->getPayment($id);

		if ( null === $payment ) {
			/**
			 *
			 * To not leak any information to malicious third parties, it is recommended
			 * to return a 200 OK response even if the ID is not known to your system.
			 *
			 * @link https://docs.mollie.com/guides/webhooks#how-to-handle-unknown-ids
			 */
			return $response;
		}

		// Update payment.
		$order_id = $payment->metadata->order_id;
		$transaction_id = $payment->id;
		$this->transaction->update_record($order_id, $transaction_id, $payment->status);

		// Add note.
		$note = \sprintf(
		/* translators: %s: Mollie */
			\__( 'Webhook requested by %s.', 'kudos' ),
			\__( 'Mollie', 'kudos' )
		);

		error_log( $note );

		return $response;
	}

}