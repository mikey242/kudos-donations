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
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\VendorInterface;
use Psr\Log\LoggerInterface;
use WP_REST_Request;
use WP_REST_Server;

class Subscription extends AbstractRestController {

	public const ROUTE_CANCEL = '/cancel';

	/**
	 * Subscription routes constructor.
	 *
	 * @param VendorInterface $vendor Current vendor.
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct( VendorInterface $vendor, LoggerInterface $logger ) {
		parent::__construct();

		$this->rest_base = 'subscription';
		$this->vendor    = $vendor;
		$this->logger    = $logger;
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
						'type'              => 'integer',
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
	public function cancel( WP_REST_Request $request ): \WP_REST_Response {

		$response = new \WP_REST_Response();

		$post_id = $request->get_param( 'id' );
		$token   = $request->get_param( 'token' );

		$this->logger->info( 'Subscription: Cancelling subscription', [ 'post_id' => $post_id ] );

		try {
			if ( ! Utils::verify_token( $post_id, $token ) ) {
				$response->set_status(401);
				$response->set_data(['message' => __('Subscription token expired.', 'kudos-donations')]);
				$this->logger->info( 'Subscription: Invalid token supplied' );
				return $response;
			}
		} catch ( Exception $e ) {
			$response->set_status(400);
			$response->set_data(['message' => __('Error canceling subscription.', 'kudos-donations')]);
			$this->logger->warning( 'Subscription: Error canceling: ' . $e->getMessage() );
			return $response;
		}

		// Get subscription post from supplied row id.
		$subscription = get_post( $post_id );

		// Cancel subscription with vendor.
		$result = $subscription && $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {
			// Update entity with canceled status.
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
			$response->set_status(200);
			$response->set_data(['message' => __( 'Subscription canceled', 'kudos-donations' )]);
		}
		return $response;
	}
}
