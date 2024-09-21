<?php
/**
 * Subscription Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller\Rest;

use Exception;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Vendor\VendorInterface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Subscription extends AbstractRestController {

	public const ROUTE_CANCEL = '/cancel';

	/**
	 * Subscription routes constructor.
	 *
	 * @param VendorInterface $vendor Current vendor.
	 */
	public function __construct( VendorInterface $vendor ) {
		parent::__construct();

		$this->rest_base = 'subscription';
		$this->vendor    = $vendor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_routes(): array {
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

		$this->logger->info( 'Subscription: Cancelling subscription', [ 'post_id' => $post_id ] );

		// Check if token is valid.
		try {
			if ( ! EncryptionService::verify_token( $post_id, $token ) ) {
				$this->logger->info( 'Subscription: Invalid token supplied' );
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

		// Get subscription post from supplied row id.
		$subscription = get_post( $post_id );

		// Cancel subscription with vendor.
		$result = $subscription && $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {
			// Cancelling was successful. Update entity with canceled status.
			SubscriptionPostType::save(
				[
					'ID' => $post_id,
					SubscriptionPostType::META_FIELD_STATUS => 'cancelled',
				]
			);

			$this->logger->info(
				'Subscription cancelled.',
				[
					'ID'              => $post_id,
					'subscription_id' => get_post_meta( $post_id, 'subscription_id', true ),
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
