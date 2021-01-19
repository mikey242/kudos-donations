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
	 * Kudos_Modal constructor.
	 *
	 * @param string|null $modal_id string
	 *
	 * @since      1.0.0
	 */
	public function __construct(string $modal_id = null) {

		$this->twig = TwigService::factory();
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
	public function get_message_modal( array $atts ) {

		$data = [
			'modal_id'    => 'kudos_modal-message-' . $this->modal_id,
			'modal_title' => isset( $atts['modal_title'] ) ? $atts['modal_title'] : '',
			'modal_text'  => isset( $atts['modal_text'] ) ? $atts['modal_text'] : '',
		];

		return $this->render_modal( '/public/modal/message.modal.html.twig', $data );

	}

	/**
	 * Get the donate modal markup
	 *
	 * @param array $data Array of data for template.
	 * @param bool $echo Whether to echo result or not.
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	public function get_donate_modal( array $data, bool $echo = false ): string {

		$data = array_merge(
			$data,
			[
				'return_url'   => Utils::get_return_url(),

				// Global settings.
				'vendor'       => Settings::get_setting( 'payment_vendor' ),
				'terms_link' => Settings::get_setting( 'terms_link' ),
			]
		);

		$out = $this->render_modal( '/public/modal/donate.modal.html.twig', $data );

		if ( $echo ) {
			echo $out;
		}

		return $out;

	}

	/**
	 * Renders the modal using twig
	 *
	 * @param string $template Template file to use.
	 * @param array $data Array of data for template.
	 *
	 * @return bool|string
	 */
	private function render_modal( string $template, array $data ): string {

		return $this->twig->render( $template, $data );

	}
}