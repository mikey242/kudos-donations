<?php
/**
 * Payment Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorFactory;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Payment extends AbstractRestController {

	public const ROUTE_CREATE            = '/create';
	public const ROUTE_REFUND            = '/refund';
	public const ROUTE_WEBHOOK           = '/webhook';
	public const ROUTE_TEST              = '/test';
	public const ROUTE_READY             = '/ready';
	public const ROUTE_STATUS            = '/status';
	public const ROUTE_RECURRING_ENABLED = '/recurring-enabled';

	private PaymentVendorInterface $vendor;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param PaymentVendorFactory $factory Current vendor.
	 */
	public function __construct( PaymentVendorFactory $factory ) {
		parent::__construct();

		$this->rest_base = 'payment';
		$this->vendor    = $factory->get_vendor();
	}

	/**
	 * Payment service routes.
	 */
	public function get_routes(): array {
		return [
			self::ROUTE_CREATE            => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'return_url'    => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'campaign_id'   => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'value'         => [
						'type'              => FieldType::NUMBER,
						'required'          => true,
						'sanitize_callback' => [ Utils::class, 'sanitize_float' ],
					],
					'name'          => [
						'type'              => FieldType::STRING,
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'email'         => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_email',
					],
					'recurring'     => [
						'type'              => FieldType::BOOLEAN,
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'business_name' => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'street'        => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'postcode'      => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'city'          => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'country'       => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'message'       => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'privacy'       => [
						'type'              => FieldType::BOOLEAN,
						'required'          => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
					'terms'         => [
						'type'              => FieldType::BOOLEAN,
						'required'          => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
				],
			],

			self::ROUTE_WEBHOOK           => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'handle_webhook' ],
				'args'                => [
					'id' => [
						'type'              => FieldType::STRING,
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
				'permission_callback' => '__return_true',
			],

			self::ROUTE_REFUND            => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'refund' ],
				'args'                => [
					'id' => [
						'type'              => FieldType::INTEGER,
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],

			self::ROUTE_TEST              => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'test_connection' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],

			self::ROUTE_READY             => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this->vendor, 'is_ready' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],

			self::ROUTE_RECURRING_ENABLED => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this->vendor, 'recurring_enabled' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],

			self::ROUTE_STATUS            => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'status' ],
				'args'                => [
					'id' => [
						'type'              => FieldType::INTEGER,
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => '__return_true',
			],
		];
	}

	/**
	 * Check the status for the supplied transaction post id.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response | WP_Error Response object.
	 */
	public function status( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'id' );
		$nonce   = $request->get_header( 'x-kudos-nonce' );

		if ( ! wp_verify_nonce( $nonce, 'order_complete' . $post_id ) ) {
			return new WP_Error( 'invalid_nonce', 'Invalid or expired nonce.' );
		}

		$transaction = get_post( $post_id );

		if ( $transaction ) {
			$data     = [
				'status'   => $transaction->{TransactionPostType::META_FIELD_STATUS},
				'currency' => $transaction->{TransactionPostType::META_FIELD_CURRENCY},
				'value'    => $transaction->{TransactionPostType::META_FIELD_VALUE},
			];
			$donor_id = $transaction->{TransactionPostType::META_FIELD_DONOR_ID};
			if ( $donor_id ) {
				$donor        = get_post( $donor_id );
				$data['name'] = $donor->{DonorPostType::META_FIELD_NAME};
			}

			return new WP_REST_Response(
				[
					'success' => true,
					'data'    => $data,
				]
			);
		}

		return new WP_Error( 'transaction_not_found', 'Transaction not found' );
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
			return new WP_REST_Response(
				[
					'message' => __( 'Request invalid.', 'kudos-donations' ),
					'nonce'   => $request->get_header( 'X-WP-Nonce' ),
				],
				400
			);
		}

		$values = $request->get_body_params();

		// Check if bot filling tabs.
		if ( $this->is_bot( $values ) ) {
			return new WP_REST_Response( [ 'message' => __( 'Request invalid.', 'kudos-donations' ) ], 400 );
		}

		$campaign = get_post( $values['campaign_id'] );

		$defaults = [
			'currency'         => $campaign->{CampaignPostType::META_FIELD_CURRENCY},
			'recurring_length' => 0,
			'return_url'       => get_site_url(),
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
				TransactionPostType::META_FIELD_VENDOR   => $this->vendor::get_slug(),
				TransactionPostType::META_FIELD_VENDOR_CUSTOMER_ID => $vendor_customer_id,
			]
		);

		// Create payment with vendor.
		$url = $this->vendor->create_payment( $args, $transaction->ID, $vendor_customer_id );

		// Return checkout url if payment successfully created.
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
				'message' => __( 'Error creating payment. Please try again later.', 'kudos-donations' ),
			],
			500
		);
	}

	/**
	 * Check the vendor api key associated with the mode. Sends a JSON response.
	 */
	public function test_connection(): WP_REST_Response {
		$result = $this->vendor->refresh();
		if ( $result ) {
			return new WP_REST_Response(
				[
					'success' => true,
					'message' =>
						__( 'Payment methods refreshed', 'kudos-donations' ),
				],
				200
			);
		} else {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' =>
						__( 'There was an error refreshing payment methods. Please check the log for more information.', 'kudos-donations' ),
				],
				200
			);
		}
	}

	/**
	 * Webhook handler. Passes request to rest_webhook method of current vendor.
	 *
	 * @param WP_REST_Request $request Request array.
	 * @return WP_ERROR | WP_REST_Response
	 */
	public function handle_webhook( WP_REST_Request $request ) {
		do_action( 'kudos_' . $this->vendor::get_slug() . '_webhook_requested', $request );
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
