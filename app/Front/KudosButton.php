<?php

namespace Kudos\Front;

use Kudos\Service\LoggerService;
use Kudos\Service\TwigService;

class KudosButton {

	/**
	 * @var TwigService
	 */
	private $twig;
	/**
	 * @var mixed|string
	 */
	private $alignment;
	/**
	 * @var bool|mixed|void
	 */
	private $label;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var LoggerService
	 */
	private $logger;
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var string
	 */
	private $amount_type;
	/**
	 * @var string
	 */
	private $fixed_amounts;
	/**
	 * @var string
	 */
	private $donation_label;

	/**
	 * KudosButton constructor.
	 *
	 * @param array $atts
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $atts ) {

		$this->twig           = new TwigService();
		$this->logger         = new LoggerService();
		$this->title          = $atts['modal_title'];
		$this->text           = $atts['welcome_text'];
		$this->label          = $atts['button_label'];
		$this->alignment      = $atts['alignment'];
		$this->amount_type    = $atts['amount_type'];
		$this->fixed_amounts  = $atts['fixed_amounts'];
		$this->donation_label = $atts['donation_label'] ?? get_the_title();
		$this->id             = uniqid( 'kudos_modal-' );

	}

	/**
	 * Get the button markup
	 *
	 * @param bool $echo
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
			'title'          => $this->title,
			'text'           => $this->text,
			'amount'         => [
				'type'         => $this->amount_type,
				'fixed_values' => explode( ',', $this->fixed_amounts ),
			],
			'donation_label' => $this->donation_label,
			'payment_by'     => __( 'Secure payment by', 'kudos-donations' ),
		];

		return $modal->get_donate_modal( $data );

	}


}

