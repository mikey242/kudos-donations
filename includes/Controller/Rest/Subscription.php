<?php
/**
 * Subscription entity rest routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller\Rest;

use Exception;
use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Repository\BaseRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorFactory;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorInterface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @extends BaseRepositoryRestController<SubscriptionEntity>
 */
class Subscription extends BaseRepositoryRestController {

	public const ROUTE_CANCEL = '/cancel';

	/**
	 * @var SubscriptionRepository
	 */
	protected BaseRepository $repository;
	private ?PaymentVendorInterface $vendor;

	/**
	 * Subscription routes constructor.
	 *
	 * @param PaymentVendorFactory   $factory Current vendor.
	 * @param SubscriptionRepository $subscription Subscription repository.
	 */
	public function __construct( PaymentVendorFactory $factory, SubscriptionRepository $subscription ) {
		$this->rest_base  = 'subscription';
		$this->repository = $subscription;
		$this->vendor     = $factory->get_vendor();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_routes(): array {
		return [
			self::ROUTE_CANCEL => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'cancel' ],
				'args'                => [
					'id'    => [
						'type'              => FieldType::INTEGER,
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
					'token' => [
						'type'     => 'string',
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( BaseEntity $item ): array {
		$item->donor       = $this->repository->get_donor( $item );
		$item->transaction = $this->repository->get_transaction( $item );
		$item->campaign    = $this->repository->get_campaign( $item );
		return (array) $item;
	}

	/**
	 * Cancel the specified subscription.
	 *
	 * @param WP_REST_Request $request Request object.
	 */
	public function cancel( WP_REST_Request $request ): WP_REST_Response {

		$post_id = $request->get_param( 'id' );
		$token   = $request->get_param( 'token' );

		// Stop if missing a parameter.
		if ( ! $post_id || ! $token ) {
			return new WP_REST_Response(
				[
					'message' => __( 'Parameter missing', 'kudos-donations' ),
				],
				422
			);
		}

		$this->logger->info( 'Cancelling subscription', [ 'post_id' => $post_id ] );

		// Check if token is valid.
		try {
			if ( ! EncryptionService::verify_token( $post_id, $token ) ) {
				$this->logger->info( 'Invalid token supplied' );
				return new WP_REST_Response(
					[
						'message' => __( 'Token expired', 'kudos-donations' ),
					],
					401
				);
			}
		} catch ( Exception $e ) {
			$this->logger->warning( 'Subscription: Error cancelling: ' . $e->getMessage() );
			return new WP_REST_Response(
				[
					'message' => __( 'Error cancelling subscription.', 'kudos-donations' ),
				],
				400
			);
		}

		/**
		 * Get subscription post from supplied row id.
		 *
		 * @var ?SubscriptionEntity $subscription
		 */
		$subscription = $this->repository->get( $post_id );

		// Cancel subscription with vendor.
		$result = $subscription && $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {
			// Cancelling was successful. Update entity with canceled status.
			$subscription->status = 'cancelled';
			$this->repository->update( $subscription );

			$this->logger->info(
				'Subscription cancelled.',
				[
					'id'              => $post_id,
					'subscription_id' => $subscription->vendor_subscription_id,
				]
			);
			return new WP_REST_Response(
				[
					'message' => __( 'Subscription canceled', 'kudos-donations' ),
				],
				200
			);
		}

		// Result from vendor was false, most likely because subscription was already cancelled.
		return new WP_REST_Response(
			[
				'message' => __( 'Subscription already canceled', 'kudos-donations' ),
			],
			200
		);
	}
}
