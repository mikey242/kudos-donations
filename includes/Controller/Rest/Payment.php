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
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Vendor\VendorInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Payment extends AbstractRestController {

	public const ROUTE_CREATE  = '/create';
	public const ROUTE_REFUND  = '/refund';
	public const ROUTE_WEBHOOK = '/webhook';
	public const ROUTE_TEST    = '/test';
	public const ROUTE_READY   = '/ready';

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param VendorInterface $vendor Current vendor.
	 */
	public function __construct( VendorInterface $vendor ) {
		parent::__construct();

		$this->rest_base = 'payment';
		$this->vendor    = $vendor;
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

			self::ROUTE_REFUND  => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'refund' ],
				'args'                => [
					'id' => [
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
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
				'permission_callback' => [ $this, 'can_manage_options' ],
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
			$donor = DonorPostType::get_post(
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
				array_merge(
					[
						'ID' => $donor->ID ?? null,
					],
					$donor_meta
				)
			);
		}

		// Create the payment. If there is no customer ID it will be un-linked.
		$vendor_customer_id = $donor->{DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID} ?? null;
		$transaction        = TransactionPostType::save(
			[
				TransactionPostType::META_FIELD_DONOR_ID => $donor->ID ?? null,
				TransactionPostType::META_FIELD_VALUE    => $args['value'],
				TransactionPostType::META_FIELD_CURRENCY => $args['currency'],
				TransactionPostType::META_FIELD_STATUS   => 'open',
				TransactionPostType::META_FIELD_MODE     => $this->vendor->get_api_mode(),
				TransactionPostType::META_FIELD_SEQUENCE_TYPE => 'true' === $args['recurring'] ? 'first' : 'oneoff',
				TransactionPostType::META_FIELD_CAMPAIGN_ID => (int) $args['campaign_id'],
				TransactionPostType::META_FIELD_MESSAGE  => $args['message'],
				TransactionPostType::META_FIELD_VENDOR   => $this->vendor::get_vendor_slug(),
				TransactionPostType::META_FIELD_VENDOR_CUSTOMER_ID => $vendor_customer_id,
			]
		);

		$url = $this->vendor->create_payment( $args, $transaction->ID, $vendor_customer_id );

		// Return checkout url if payment successfully created in Mollie.
		if ( $url ) {

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

	/**
	 * Refund the transaction for the post id provided.
	 *
	 * @param WP_REST_Request $request The request.
	 */
	public function refund( WP_REST_Request $request ): bool {
		$post_id = $request->get_param( 'id' );
		return $this->vendor->refund( $post_id );
	}
}
