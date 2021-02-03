<?php

namespace Kudos\Front;

use Kudos\Service\TwigService;

class KudosButton {

	/**
	 * Instance of TwigService
	 *
	 * @var TwigService
	 */
	private $twig;
	/**
	 * Text alignment
	 *
	 * @var mixed|string
	 */
	private $alignment;
	/**
	 * Button label
	 *
	 * @var bool|mixed|void
	 */
	private $button_label;
	/**
	 * Modal id
	 *
	 * @var string
	 */
	private $target_id;
	/**
	 * @var array
	 */
	private $campaign;

	/**
	 * KudosButton constructor.
	 *
	 * @param array $atts Array of above attributes.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $atts ) {

		$this->twig      = TwigService::factory();
		$this->target_id = uniqid( 'kudos_modal-' );

		// Assign atts to properties
		foreach ( $atts as $property => $value ) {
			if ( property_exists( static::class, $property ) ) {
				$this->$property = $value;
			}
		}

	}

	/**
	 * Gets the button and modal markup.
	 *
	 * @return string|null
	 * @since 2.3.2
	 */
	public function get_markup(): ?string {

		$button = $this->get_button();
		$modal = $this->get_donate_modal();

		if ( ! empty( $modal ) && ! empty( $button ) ) {
			return $this->get_button() . $this->get_donate_modal();
		}

		return null;

	}

	/**
	 * Get the button markup
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	private function get_button(): string {

		$data = [
			'alignment' => $this->alignment,
			'label'     => $this->button_label,
			'target'    => $this->target_id,
		];

		return $this->twig->render( 'public/kudos.button.html.twig', $data );
	}

	/**
	 * Get the donate modal markup
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	private function get_donate_modal(): string {

		$modal = new KudosModal( $this->target_id );

		$data = [
			'modal_id'         => $this->target_id,
			'campaign'         => $this->campaign,
			'payment_by'       => __( 'Secure payment by', 'kudos-donations' ),
		];

		return $modal->get_donate_modal( $data );

	}


}
