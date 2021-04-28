<?php

namespace Kudos\Front;

use Exception;
use Kudos\Helpers\Campaigns;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\TwigService;

class KudosModal {

	const MESSAGE_TEMPLATE = '/public/modal/message.modal.html.twig';
	const DONATE_TEMPLATE = '/public/modal/donate.modal.html.twig';

	/**
	 * Instance of twig service.
	 *
	 * @var TwigService
	 */
	private $twig;

	/**
	 * The id of the modal
	 *
	 * @var string
	 */
	private $modal_id;

	/**
	 * The template file to use.
	 *
	 * @var string
	 */
	private $template;

	/**
	 * The data to pass to the current template.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * KudosModal constructor.
	 *
	 * @param string|null $modal_id string
	 *
	 * @since      1.0.0
	 */
	public function __construct( string $modal_id = null ) {

		$this->twig     = TwigService::factory();
		$this->modal_id = $modal_id ?? uniqid();

	}

	/**
	 * Returns the markup for the current modal.
	 *
	 * @return string
	 */
	private function get_markup(): string {

		return $this->twig->render( $this->template, $this->data );

	}

	/**
	 * Get message modal markup.
	 *
	 * @param string $title
	 * @param string|null $message
	 *
	 * @return string
	 * @since      1.0.0
	 */
	public function create_message_modal( string $title, string $message=null ): string {

		$this->template = self::MESSAGE_TEMPLATE;

		$this->data = apply_filters( 'kudos_message_modal_data',
			[
				'modal_id'    => 'kudos_modal-message-' . $this->modal_id,
				'modal_title' => $title ?? '',
				'modal_text'  => $message ?? '',
			] );

		return Front::kudos_render( $this->get_markup() );

	}

	/**
	 * Get the donate modal markup.
	 *
	 * @param string $campaign_id Campaign id to create modal for.
	 *
	 * @return string
	 * @throws Exception
	 * @since    1.0.0
	 */
	public function create_donate_modal( string $campaign_id ): ?string {

		$campaigns = new Campaigns();
		$campaign  = $campaigns->get_campaign( $campaign_id );

		// Check if there is a campaign, otherwise throw an exception.
		if ( empty( $campaign ) ) {
			/* translators: %s: Campaign id */
			throw new Exception( sprintf( __( 'Campaign "%s" not found.', 'kudos-donations' ), $campaign_id ) );
		}

		$campaign['total'] = $campaigns::get_campaign_stats( $campaign_id )['total'];
		$this->template    = self::DONATE_TEMPLATE;
		$vendor_settings   = Settings::get_current_vendor_settings();

		// Merge global settings with provided data
		$this->data = apply_filters( 'kudos_donate_modal_data',
			[
				'modal_id'          => $this->modal_id,
				'campaign'          => $campaign,
				'return_url'        => Utils::get_return_url(),
				'vendor'            => Settings::get_setting( 'payment_vendor' ),
				'privacy_link'      => Settings::get_setting( 'privacy_link' ),
				'terms_link'        => Settings::get_setting( 'terms_link' ),
				'recurring_allowed' => $vendor_settings['recurring'] ?? false,
				'logo_url'          => Utils::get_logo_url(),
				'spinner'           => Utils::get_kudos_logo_markup( 'black', 30 ),
			]
		);

		return $this->get_markup();

	}
}