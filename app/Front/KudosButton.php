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
	 * Modal title
	 *
	 * @var string
	 */
	private $modal_title;
	/**
	 * Modal text
	 *
	 * @var string
	 */
	private $welcome_text;
	/**
	 * Donation amount selection type
	 *
	 * @var string
	 */
	private $amount_type;
	/**
	 * Fixed amount list (5, 10, 15)
	 *
	 * @var string
	 */
	private $fixed_amounts;
	/**
	 * Campaign label
	 *
	 * @var string
	 */
	private $campaign_id;
	/**
	 * @var string
	 */
	private $campaign_goal;
	/**
	 * @var string
	 */
	private $campaign_total;
	/**
	 * @var string
	 */
	private $donation_type;
	/**
	 * @var bool
	 */
	private $address_enabled;
	/**
	 * @var bool
	 */
	private $address_required;

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
			'modal_title'      => $this->modal_title,
			'modal_text'       => $this->welcome_text,
			'amount_type'      => $this->amount_type,
			'fixed_amounts'    => array_slice( explode( ',', $this->fixed_amounts ), 0, 4 ),
			'address_enabled'  => $this->address_enabled,
			'address_required' => $this->address_required,
			'donation_type'    => $this->donation_type,
			'campaign_id'      => $this->campaign_id,
			'campaign_goal'    => $this->campaign_goal,
			'campaign_total'   => $this->campaign_total,
			'payment_by'       => __( 'Secure payment by', 'kudos-donations' ),
		];

		return $modal->get_donate_modal( $data );

	}


}
