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
	private $label;
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
	private $title;
	/**
	 * Modal text
	 *
	 * @var string
	 */
	private $text;
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
	 * KudosButton constructor.
	 *
	 * @param array $atts Array of above attributes.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $atts ) {

		$this->twig           = TwigService::factory();
		$this->title          = $atts['modal_title'];
		$this->text           = $atts['welcome_text'];
		$this->label          = $atts['button_label'];
		$this->alignment      = $atts['alignment'];
		$this->amount_type    = $atts['amount_type'];
		$this->fixed_amounts  = $atts['fixed_amounts'];
		$this->campaign_label = $atts['campaign_label'];
		$this->id             = uniqid( 'kudos_modal-' );

	}

	/**
	 * Get the button markup
	 *
	 * @param bool $echo Whether to echo result or not.
	 *
	 * @return string|void
	 * @since    1.0.0
	 */
	public function get_button( bool $echo = true ) {

		$data = [
			'alignment' => $this->alignment,
			'label'     => $this->label,
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
	public function get_donate_modal() {

		$modal = new KudosModal();

		$data = [
			'modal_id'       => $this->id,
			'modal_title'    => $this->title,
			'modal_text'     => $this->text,
			'amount'         => [
				'type'         => $this->amount_type,
				'fixed_values' => explode( ',', $this->fixed_amounts ),
			],
			'campaign_label' => $this->campaign_label,
			'payment_by'     => __( 'Secure payment by', 'kudos-donations' ),
		];

		return $modal->get_donate_modal( $data );

	}


}
