<?php

namespace Kudos\Front;

use Exception;
use Kudos\Helpers\Utils;

class KudosButton extends AbstractRender {

	const TEMPLATE = 'public/kudos.button.html.twig';

	/**
	 * Text alignment.
	 *
	 * @var mixed|string
	 */
	protected $alignment;
	/**
	 * Button label.
	 *
	 * @var bool|mixed|void
	 */
	protected $button_label;
	/**
	 * @var string
	 */
	protected $campaign_id;
	/**
	 * @var KudosModal
	 */
	protected $modal;
	/**
	 * @var mixed|void
	 */
	protected $logo;
	/**
	 * @var KudosForm
	 */
	protected $form;

	/**
	 * KudosButton constructor.
	 *
	 * @param array $atts Array of above attributes.
	 *
	 * @throws Exception
	 * @since    1.0.0
	 */
	public function __construct( array $atts ) {

		parent::__construct();

		$this->template = self::TEMPLATE;
		$this->logo = apply_filters( 'kudos_get_button_logo', Utils::get_kudos_logo_markup( 'white' ) );

		// Assign button atts to properties.
		foreach ( $atts as $property => $value ) {
			if ( property_exists( static::class, $property ) ) {
				$this->$property = $value;
			}
		}

		// Generate associated form and modal.
		$this->create_modal();

	}

	/**
	 * Creates the donation modal and assigns it the form as content.
	 *
	 * @throws \Exception
	 */
	private function create_modal() {

		$form = new KudosForm($this->campaign_id);
		$this->modal = new KudosModal($this->id);
		$this->modal->create_donate_modal($form);
	}
}
