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
	 * Button id
	 *
	 * @var string
	 */
	private $id;
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
	private $campaign_label;
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

		$this->twig             = TwigService::factory();
		$this->id               = uniqid( 'kudos_modal-' );

		foreach ( $atts as $property => $value ) {
			if ( property_exists( static::class, $property ) ) {
				$this->$property = $value;
			}
		}

	}

	/**
	 * Get the button markup
	 *
	 * @param bool $echo Whether to echo result or not.
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	public function get_button( bool $echo = true ): string {

		$data = [
			'alignment' => $this->alignment,
			'label'     => $this->button_label,
			'target'    => $this->id,
		];

		$out = $this->twig->render( 'public/kudos.button.html.twig', $data );

		if ( $echo ) {
			echo $out;
		}

		return $out;
	}

	/**
	 * Get the donate modal markup
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	public function get_donate_modal(): string {

		$modal = new KudosModal();
		$allowed_types = ['fixed', 'open', 'both'];

		$data = [
			'modal_id'          => $this->id,
			'modal_title'       => $this->modal_title,
			'modal_text'        => $this->welcome_text,
			'amount_type'       => in_array( $this->amount_type, $allowed_types, true ) ? $this->amount_type : 'open',
			'fixed_amounts'     => array_slice(explode( ',', $this->fixed_amounts ),0, 4),
			'address_enabled'   => $this->address_enabled,
			'address_required'  => $this->address_required,
			'donation_type'     => $this->donation_type,
			'campaign_label'    => $this->campaign_label,
			'payment_by'        => __( 'Secure payment by', 'kudos-donations' ),
		];

		return $modal->get_donate_modal( $data );

	}


}
