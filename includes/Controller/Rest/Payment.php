<?php
/**
 * Payment Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SanitizeTrait;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderInterface;
use IseardMedia\Kudos\Service\EncryptionService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Payment extends BaseRestController {

	use SanitizeTrait;

	protected string $rest_base = 'payment';

	public const ROUTE_CREATE  = '/create';
	public const ROUTE_REFUND  = '/refund';
	public const ROUTE_WEBHOOK = '/webhook';
	public const ROUTE_TEST    = '/test';
	public const ROUTE_STATUS  = '/status';

	private PaymentProviderFactory $factory;
	private PaymentProviderInterface $vendor;
	private TransactionRepository $transaction_repository;
	private DonorRepository $donor_repository;
	private CampaignRepository $campaign_repository;

	/**
	 * Payment constructor.
	 *
	 * @param PaymentProviderFactory $factory Payment provider factory.
	 * @param TransactionRepository  $transaction_repository Transaction repository.
	 * @param DonorRepository        $donor_repository Donor repository.
	 * @param CampaignRepository     $campaign_repository Campaign repository.
	 */
	public function __construct( PaymentProviderFactory $factory, TransactionRepository $transaction_repository, DonorRepository $donor_repository, CampaignRepository $campaign_repository ) {
		$this->factory                = $factory;
		$this->transaction_repository = $transaction_repository;
		$this->donor_repository       = $donor_repository;
		$this->campaign_repository    = $campaign_repository;
	}

	/**
	 * Resolves the active payment provider on first use.
	 *
	 * Deliberately lazy: resolving the provider eagerly (e.g. in the constructor) runs its
	 * setup() during container bootstrap — before `init` — which decrypts API keys and loads
	 * translations too early. Route callbacks run on rest_api_init, well after setup is safe.
	 */
	private function get_vendor(): PaymentProviderInterface {
		if ( ! isset( $this->vendor ) ) {
			$this->vendor = $this->factory->get_provider();
		}
		return $this->vendor;
	}

	/**
	 * Payment service routes.
	 */
	public function get_routes(): array {
		return [
			self::ROUTE_CREATE => [
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
						'sanitize_callback' => [ $this, 'sanitize_float' ],
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
					'language'      => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			],

			self::ROUTE_WEBHOOK . '(?:/(?P<vendor>[a-z0-9_-]+))?' => [
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

			self::ROUTE_REFUND => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'refund' ],
				'args'                => [
					'id' => [
						'type'              => FieldType::INTEGER,
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],

			self::ROUTE_TEST   => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'test_connection' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],

			self::ROUTE_STATUS => [
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
	 * Check the status for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response | WP_Error Response object.
	 */
	public function status( WP_REST_Request $request ) {
		$entity_id = $request->get_param( 'id' );
		$nonce     = $request->get_header( 'x-kudos-nonce' );

		if ( ! EncryptionService::verify_token( 'order_complete' . $entity_id, $nonce ) ) {
			return new WP_Error( 'invalid_nonce', 'Invalid or expired nonce.' );
		}

		$transaction = $this->transaction_repository->get( $entity_id );
		if ( null === $transaction ) {
			return new WP_Error( 'transaction_not_found', 'Transaction not found' );
		}

		$provider = $this->factory->get_provider( $transaction->vendor );
		if ( null === $provider ) {
			return new WP_Error( 'provider_not_found', 'Provider not found' );
		}

		// Refresh from the vendor; fall back to the stored transaction when there is nothing to sync.
		$transaction = $provider->sync_transaction_status( $entity_id ) ?? $transaction;

		$data     = [
			'status'   => $transaction->status,
			'currency' => $transaction->currency,
			'value'    => $transaction->value,
		];
		$donor_id = $transaction->donor_id;
		if ( $donor_id ) {
			/** @var DonorEntity $donor */
			$donor        = $this->donor_repository->get( $donor_id );
			$data['name'] = $donor->name;
		}

		return new WP_REST_Response(
			[
				'success' => true,
				'data'    => $data,
			],
			200
		);
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( WP_REST_Request $request ): WP_REST_Response {
		$values = $request->get_body_params();

		// Check if bot filling tabs.
		if ( $this->is_bot( $values ) ) {
			return new WP_REST_Response( [ 'message' => __( 'Request invalid.', 'kudos-donations' ) ], 400 );
		}

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repository->get( (int) $values['campaign_id'] );

		$defaults = [
			'currency'         => $campaign->currency,
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
			'language'         => 'en_US',
		];

		$args = wp_parse_args( $values, $defaults );

		$vendor = $this->get_vendor();

		// Add submit action and pass args.
		do_action( 'kudos_submit_payment', $args );

		// If email found, try to find an existing customer or create a new one.
		$donor    = null;
		$donor_id = null;
		if ( $args['email'] ) {

			$donor_args = [
				'mode'          => $vendor->get_api_mode(),
				'vendor'        => $vendor::get_slug(),
				'email'         => $args['email'],
				'name'          => $args['name'],
				'business_name' => $args['business_name'],
				'street'        => $args['street'],
				'postcode'      => $args['postcode'],
				'city'          => $args['city'],
				'country'       => $args['country'],
				'locale'        => Utils::normalize_locale( $args['language'] ),
			];

			// Search for existing donor based on email, mode, and vendor.
			$donor = $this->donor_repository->find_one_by(
				[
					'email'  => $args['email'],
					'mode'   => $vendor->get_api_mode(),
					'vendor' => $vendor::get_slug(),
				]
			);

			if ( empty( $donor ) ) {
				// Create new customer with vendor if none found.
				$customer = $vendor->create_customer( $args['email'], $args['name'] );
				if ( false !== $customer ) {
					$donor_args['vendor_customer_id'] = $customer->id;
				}
				$donor = $this->donor_repository->new_entity( $donor_args );
			} else {
				// Otherwise update existing donor object.
				$donor->merge( $donor_args );
			}

			// Save donor and fetch updated record.
			$donor_id = $this->donor_repository->upsert( $donor );
		}

		// Create the payment. If there is no customer ID it will be un-linked.
		$vendor_customer_id = $donor ? $donor->vendor_customer_id : null;
		$transaction        = $this->transaction_repository->new_entity(
			[
				'donor_id'      => $donor_id ?? null,
				'value'         => $args['value'],
				'currency'      => $args['currency'],
				'status'        => PaymentStatus::OPEN,
				'mode'          => $vendor->get_api_mode(),
				'sequence_type' => 'true' === $args['recurring'] ? 'first' : 'oneoff',
				'campaign_id'   => $args['campaign_id'],
				'message'       => $args['message'],
				'vendor'        => $vendor::get_slug(),
			]
		);

		$transaction_id = $this->transaction_repository->insert( $transaction );
		$transaction    = $this->transaction_repository->get( $transaction_id );

		// Append order_complete query args if the campaign is configured to show a return message.
		if ( $campaign->show_return_message ) {
			$action             = 'order_complete';
			$args['return_url'] = add_query_arg(
				[
					'kudos_action'         => $action,
					'kudos_transaction_id' => $transaction_id,
					'kudos_nonce'          => EncryptionService::generate_token( $action . $transaction_id ),
				],
				$args['return_url']
			);
		}

		// Create payment with vendor.
		$url = $vendor->create_payment( $args, $transaction, $vendor_customer_id );

		// Return checkout url if payment successfully created.
		if ( $url ) {
			Utils::schedule_action(
				strtotime( '+10 minutes' ),
				'kudos_check_payment_status',
				[ $transaction_id ]
			);

			// Send payment redirect URL.
			return new WP_REST_Response(
				[
					'success' => true,
					'url'     => $url,
				],
				200
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
		$result = $this->get_vendor()->refresh();
		if ( $result ) {
			return new WP_REST_Response(
				[
					'success' => true,
					'message' =>
						__( 'Payment methods refreshed', 'kudos-donations' ),
				],
				200
			);
		}

		return new WP_REST_Response(
			[
				'success' => false,
				'message' =>
					__( 'There was an error refreshing payment methods. Please check the log for more information.', 'kudos-donations' ),
			],
			500
		);
	}

	/**
	 * Webhook handler. Routes the request to the provider named in the URL, so payments from a
	 * previously active provider keep being processed after the site switches vendor. Requests
	 * without a vendor slug predate per-vendor URLs and are always Mollie.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function handle_webhook( WP_REST_Request $request ): WP_REST_Response {
		$vendor   = $request->get_param( 'vendor' );
		$slug     = empty( $vendor ) ? 'mollie' : $vendor;
		$provider = $this->factory->get_provider( $slug );

		if ( null === $provider ) {
			$this->get_logger()->warning( 'Webhook received for unknown vendor.', [ 'vendor' => $slug ] );
			return new WP_REST_Response( [ 'success' => false ], 404 );
		}

		do_action( 'kudos_' . $provider::get_slug() . '_webhook_requested', $request );

		return $provider->rest_webhook( $request );
	}

	/**
	 * Refund the transaction for the post id provided.
	 *
	 * @param WP_REST_Request $request The request.
	 */
	public function refund( WP_REST_Request $request ): WP_REST_Response {
		$entity_id   = $request->get_param( 'id' );
		$transaction = $this->transaction_repository->get( $entity_id );
		$provider    = null !== $transaction ? $this->factory->get_provider( $transaction->vendor ) : null;

		if ( null !== $provider && $provider->refund( $entity_id ) ) {
			return new WP_REST_Response( [ 'message' => __( 'Refund successful.', 'kudos-donations' ) ], 200 );
		}

		return new WP_REST_Response( [ 'message' => __( 'Refund failed.', 'kudos-donations' ) ], 500 );
	}
}
