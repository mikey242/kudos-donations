<?php
/**
 * Payment Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Vendor\VendorInterface;
use Psr\Log\LoggerInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Payment extends AbstractRestController {

	public const ROUTE_CREATE  = '/create';
	public const ROUTE_WEBHOOK = '/webhook';
	public const ROUTE_TEST    = '/test';
	public const ROUTE_READY   = '/ready';

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param VendorInterface $vendor Current vendor.
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct( VendorInterface $vendor, LoggerInterface $logger ) {
		parent::__construct();

		$this->rest_base = 'payment';
		$this->vendor    = $vendor;
		$this->logger    = $logger;
	}

	/**
	 * Payment service routes.
	 *
	 * @return array
	 */
	public function get_routes(): array {
		return [
			self::ROUTE_CREATE  => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'return_url'    => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'campaign_id'   => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'value'         => [
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
					'name'          => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'email'         => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_email',
					],
					'recurring'     => [
						'type'              => 'boolean',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'business_name' => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'street'        => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'postcode'      => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'city'          => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'country'       => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'message'       => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'privacy'       => [
						'type'              => 'boolean',
						'required'          => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
					'terms'         => [
						'type'              => 'boolean',
						'required'          => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
				],
			],

			self::ROUTE_WEBHOOK => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'handle_webhook' ],
				'args'                => [
					'id' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
				'permission_callback' => '__return_true',
			],

			self::ROUTE_TEST    => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'test_connection' ],
				'args'                => [
					'keys' => [
						'type'     => 'object',
						'required' => true,
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],

			self::ROUTE_READY   => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this->vendor, 'is_ready' ],
				'permission_callback' => '__return_true',
			],
		];
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ): WP_REST_Response {
		// Verify nonce.
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Request invalid.', 'kudos-donations' ),
					'nonce'   => $request->get_header( 'X-WP-Nonce' ),
				]
			);
		}

		$values = $request->get_body_params();

		// Check if bot filling tabs.
		if ( $this->is_bot( $values ) ) {
			wp_send_json_error( [ 'message' => __( 'Request invalid.', 'kudos-donations' ) ] );
		}

		$defaults = [
			'currency'         => 'EUR',
			'recurring_length' => 0,
			'redirect_url'     => get_site_url(),
			'name'             => null,
			'business_name'    => null,
			'email'            => null,
			'street'           => null,
			'postcode'         => null,
			'city'             => null,
			'country'          => null,
			'message'          => null,
			'campaign_id'      => null,
		];

		$args = wp_parse_args( $values, $defaults );

		// Add submit action and pass args.
		do_action( 'kudos_submit_payment', $args );

		// If email found, try to find an existing customer or create a new one.
		if ( $args['email'] ) {

			$donor_meta = [
				DonorPostType::META_FIELD_MODE          => $this->vendor->get_api_mode(),
				DonorPostType::META_FIELD_EMAIL         => $args['email'],
				DonorPostType::META_FIELD_NAME          => $args['name'],
				DonorPostType::META_FIELD_BUSINESS_NAME => $args['business_name'],
				DonorPostType::META_FIELD_STREET        => $args['street'],
				DonorPostType::META_FIELD_POSTCODE      => $args['postcode'],
				DonorPostType::META_FIELD_CITY          => $args['city'],
				DonorPostType::META_FIELD_COUNTRY       => $args['country'],
			];

			// Search for existing donor based on email and mode.
			$donor = DonorPostType::get_one_by_meta(
				[
					DonorPostType::META_FIELD_EMAIL => $args['email'],
					DonorPostType::META_FIELD_MODE  => $this->vendor->get_api_mode(),
				]
			);

			// Create new customer with vendor if none found.
			if ( ! $donor ) {
				$customer = $this->vendor->create_customer( $args['email'], $args['name'] );
				$donor_meta[ DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID ] = $customer->id;
			}

			// Update or create donor.
			$donor = DonorPostType::save(
				[
					'ID' => $donor->ID ?? 0,
				],
				$donor_meta
			);
		}

		// Create the payment. If there is no customer ID it will be un-linked.
		$vendor_customer_id = get_post_meta( $donor->ID, DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID, true ) ?? null;
		$transaction        = TransactionPostType::save();
		$url                = $this->vendor->create_payment( $args, $transaction->ID, $vendor_customer_id );

		// Return checkout url if payment successfully created in Mollie.
		if ( $url ) {
			do_action( 'kudos_payment_submit_successful', $args );
			$title = TransactionPostType::get_formatted_id( $transaction->ID );
			TransactionPostType::save(
				[
					'ID'         => $transaction->ID,
					'post_title' => $title,
				],
				[
					TransactionPostType::META_FIELD_DONOR_ID => $donor->ID ?? null,
					TransactionPostType::META_FIELD_VALUE  => $args['value'],
					TransactionPostType::META_FIELD_CURRENCY => $args['currency'],
					TransactionPostType::META_FIELD_STATUS => 'open',
					TransactionPostType::META_FIELD_MODE   => $this->vendor->get_api_mode(),
					TransactionPostType::META_FIELD_SEQUENCE_TYPE => 'true' === $args['recurring'] ? 'first' : 'oneoff',
					TransactionPostType::META_FIELD_CAMPAIGN_ID => (int) $args['campaign_id'],
					TransactionPostType::META_FIELD_MESSAGE => $args['message'],
					TransactionPostType::META_FIELD_VENDOR => $this->vendor::get_vendor_slug(),
					TransactionPostType::META_FIELD_VENDOR_CUSTOMER_ID => $vendor_customer_id,
				]
			);

			// Send payment redirect URL.
			return new WP_REST_Response(
				[
					'success' => true,
					'url'     => $url,
				]
			);
		}

		// If payment not created return an error message.
		return new WP_REST_Response(
			[
				'success' => false,
				'message' => __( 'Error creating Mollie payment. Please try again later.', 'kudos-donations' ),
			],
			500
		);
	}

	/**
	 * Cancel the specified subscription.
	 *
	 * @param string $id subscription row ID.
	 */
	public function cancel_subscription( string $id ): bool {

		// Get subscription post from supplied row id.
		$subscription = get_post( $id );

		// Cancel subscription with vendor.
		$result = $subscription && $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {
			// Update entity with canceled status.
			SubscriptionPostType::update_meta(
				$id,
				[
					SubscriptionPostType::META_FIELD_STATUS => 'cancelled',
				]
			);

			$this->logger->info(
				'Subscription cancelled.',
				[
					'id'              => $id,
					'subscription_id' => get_post_meta( $id, 'subscription_id', true ),
				]
			);

			return true;
		}

		return false;
	}

	/**
	 * Check the vendor api key associated with the mode. Sends a JSON response.
	 *
	 * @param WP_REST_Request $request Instance of WP_REST_Request.
	 */
	public function test_connection( WP_REST_Request $request ): WP_REST_Response {
		return $this->vendor->verify_connection( $request );
	}

	/**
	 * Webhook handler. Passes request to rest_webhook method of current vendor.
	 *
	 * @param WP_REST_Request $request Request array.
	 * @return WP_ERROR | WP_REST_Response
	 */
	public function handle_webhook( WP_REST_Request $request ) {
		return $this->vendor->rest_webhook( $request );
	}
}
