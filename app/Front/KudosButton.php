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
	 * @var string
	 */
	private $campaign_id;
	/**
	 * @var string
	 */
	private $type;
	/**
	 * @var KudosModal
	 */
	private $modal;

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
		$this->modal     = new KudosModal( $this->target_id );

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

		$button = $this->twig->render( 'public/kudos.button.html.twig',
			[
				'alignment' => $this->alignment,
				'label'     => $this->button_label,
				'target'    => $this->target_id,
				'type'      => in_array( $this->type, [ 'shortcode', 'block' ] ) ? $this->type : null,
			] );

		$modal = $this->campaign_id ? $this->modal->create_donate_modal( $this->campaign_id ) : null;

		if ( ! empty( $button ) && ! empty( $modal ) ) {
			return $button . $modal;
		}

		return null;

	}

}
