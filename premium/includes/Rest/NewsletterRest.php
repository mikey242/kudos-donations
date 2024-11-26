<?php
/**
 * Newsletter Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\KudosPremium\Rest;

use IseardMedia\Kudos\Controller\Rest\AbstractRestController;
use IseardMedia\KudosPremium\NewsletterProvider\NewsletterProviderFactory;
use IseardMedia\KudosPremium\NewsletterProvider\NewsletterProviderInterface;
use WP_REST_Response;
use WP_REST_Server;

class NewsletterRest extends AbstractRestController {

	private ?NewsletterProviderInterface $provider;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param ?NewsletterProviderInterface $provider The newsletter provider service.
	 */
	public function __construct( ?NewsletterProviderInterface $provider ) {
		parent::__construct();

		$this->rest_base = 'newsletter';
		$this->provider  = $provider;
	}

	/**
	 * Newsletter routes.
	 */
	public function get_routes(): array {
		return [
			'/refresh'   => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'refresh' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
			'/providers' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_providers' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Refresh the current provider.
	 */
	public function refresh(): WP_REST_Response {
		$result = $this->provider->refresh();
		if ( $result ) {
			return new WP_REST_Response(
				[
					// translators: %s is the newsletter provider (e.g. Mailchimp).
					'message' => wp_sprintf( __( '%s refreshed', 'kudos-donations' ), $this->provider->get_name() ),
				],
				200
			);
		}
		return new WP_REST_Response(
			[
				// translators: %s is the newsletter provider (e.g. Mailchimp).
				'message' => wp_sprintf( __( 'Error refreshing %s', 'kudos-donations' ), $this->provider->get_name() ),
			],
		);
	}

	/**
	 * Get a list of the supported providers.
	 */
	public function get_providers(): WP_REST_Response {
		$providers   = NewsletterProviderFactory::get_providers();
		$transformed = array_map(
			fn( $key, $value ) =>
				[
					'label' => $value['label'],
					'slug'  => $key,
				],
			array_keys( $providers ),
			$providers
		);
		return new WP_REST_Response(
			[
				'providers' => $transformed,
			],
			200
		);
	}
}
