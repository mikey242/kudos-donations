<?php

namespace Kudos\Front;

use Kudos\Helpers\Settings;
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
	 * @var mixed
	 */
	private $donation_type;

	/**
	 * @var array
	 */
	private $address;

	/**
	 * KudosButton constructor.
	 *
	 * @param array $atts Array of above attributes.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $atts ) {

		$this->twig           = TwigService::factory();
		$this->id             = uniqid( 'kudos_modal-' );
		$this->label          = $atts['button_label'];
		$this->alignment      = $atts['alignment'];

		if(isset($atts['campaign_id'])) {
			$campaign = Settings::get_campaign($atts['campaign_id']);
		}

		$this->address = [
			'enabled' => !empty($campaign['address_enabled']) ?? false,
		    'required' => !empty($campaign['address_required']) ?? false
		];

		$this->donation_type  = isset($campaign['donation_type']) ? $campaign['donation_type'] : 'both';
		$this->title          = isset($campaign['modal_title']) ? $campaign['modal_title'] : '' ;
		$this->text           = isset($campaign['welcome_text']) ? $campaign['welcome_text'] : '' ;
		$this->amount_type    = isset($campaign['amount_type']) ? $campaign['amount_type'] : '' ;
		$this->fixed_amounts  = isset($campaign['fixed_amounts']) ? $campaign['fixed_amounts'] : '' ;


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
	public function get_donate_modal(): string {

		$modal = new KudosModal();
		$allowed_types = ['fixed', 'open', 'both'];

		$data = [
			'modal_id'       => $this->id,
			'modal_title'    => $this->title,
			'modal_text'     => $this->text,
			'amount'         => [
				'type'         => in_array( $this->amount_type, $allowed_types, true ) ? $this->amount_type : 'open',
				'fixed_amounts' => array_slice(explode( ',', $this->fixed_amounts ),0, 4),
			],
			'address'      => $this->address,
			'donation_type' => $this->donation_type,
			'campaign_label' => $this->campaign_label,
			'payment_by'     => __( 'Secure payment by', 'kudos-donations' ),
		];

		return $modal->get_donate_modal( $data );

	}


}
