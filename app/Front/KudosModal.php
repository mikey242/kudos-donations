<?php

namespace Kudos\Front;

use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\TwigService;

class KudosModal {

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
	 * Get message modal markup
	 *
	 * @param array $atts Message modal attributes.
	 *
	 * @return string|bool
	 * @since      1.0.0
	 */
	public function get_message_modal( array $atts ): string {

		$data = [
			'modal_id'    => 'kudos_modal-message-' . $this->modal_id,
			'modal_title' => isset( $atts['modal_title'] ) ? $atts['modal_title'] : '',
			'modal_text'  => isset( $atts['modal_text'] ) ? $atts['modal_text'] : '',
		];

		return $this->twig->render( '/public/modal/message.modal.html.twig',
			apply_filters( 'kudos_message_modal_data', $data ) );

	}

	/**
	 * Get the donate modal markup
	 *
	 * @param array $data Array of data for template.
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	public function get_donate_modal( array $data ): string {

		$vendor_settings = Settings::get_current_vendor_settings();
		$link_classes = "kd-text-primary hover:kd-text-primary-dark kd-shadow-none kd-underline kd-cursor-pointer kd-ml-1";
		$privacy_link = Settings::get_setting('privacy_link');
		$terms_link = Settings::get_setting( 'terms_link' );

		// Merge global settings with provided data
		$data = array_merge( $data,
			[
				'return_url'        => Utils::get_return_url(),
				'vendor'            => Settings::get_setting( 'payment_vendor' ),
				'privacy_link'      => sprintf(__( 'I agree with the %1$s privacy policy %2$s.', 'kudos-donations' ),"<a class='$link_classes' target='_blank' href='$privacy_link' >", "</a>"),
				'terms_link'        => sprintf(__( 'I agree with the %1$s terms and conditions %2$s.', 'kudos-donations' ),"<a class='$link_classes' target='_blank' href='$terms_link' >", "</a>"),
				'recurring_allowed' => isset( $vendor_settings['recurring'] ) ? $vendor_settings['recurring'] : false,
			]
		);

		return $this->twig->render( '/public/modal/donate.modal.html.twig',
			apply_filters( 'kudos_donate_modal_data', $data ) );

	}
}