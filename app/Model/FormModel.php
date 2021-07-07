<?php

namespace Kudos\Model;

use Exception;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;

class FormModel extends AbstractModel {

	const TEMPLATE = 'public/forms/donate.form.html.twig';

	/**
	 * @var string
	 */
	protected $return_url;
	/**
	 * @var bool
	 */
	protected $recurring_allowed;
	/**
	 * @var array
	 */
	protected $campaign_id;
	/**
	 * @var string
	 */
	protected $welcome_title;
	/**
	 * @var string
	 */
	protected $welcome_text;
	/**
	 * @var string
	 */
	protected $amount_type;
	/**
	 * @var string
	 */
	protected $fixed_amounts;
	/**
	 * @var string
	 */
	protected $frequency;
	/**
	 * @var bool
	 */
	protected $address_enabled;
	/**
	 * @var bool
	 */
	protected $address_required;
	/**
	 * @var bool
	 */
	protected $message_enabled;
	/**
	 * @var string
	 */
	protected $privacy_link;
	/**
	 * @var string
	 */
	protected $terms_link;
	/**
	 * @var mixed|string
	 */
	protected $campaign_goal;
	/**
	 * @var mixed|string
	 */
	protected $show_progress;
	/**
	 * @var string
	 */
	protected $campaign_stats;
	/**
	 * @var mixed
	 */
	protected $vendor_name;
	/**
	 * @var mixed|string
	 */
	protected $button_label;
	/**
	 * @var string|null
	 */
	protected $spinner;

	/**
	 * KudosForm constructor.
	 *
	 * @param string $campaign_id
	 *
	 * @throws Exception
	 */
	public function __construct( string $campaign_id ) {

		parent::__construct();

		// Throws exception if campaign not found.
		$campaign = Settings::get_campaign( $campaign_id );

		// Configure global properties.
		$this->template          = self::TEMPLATE;
		$this->campaign_id       = $campaign_id;
		$this->return_url        = Utils::get_return_url();
		$this->privacy_link      = Settings::get_setting( 'privacy_link' );
		$this->terms_link        = Settings::get_setting( 'terms_link' );
		$this->recurring_allowed = isset( Settings::get_current_vendor_settings()['recurring'] ) ?? false;
		$this->vendor_name       = Settings::get_setting( 'payment_vendor' );
		$this->spinner     = Utils::get_kudos_logo_markup( 'black', 30 );

		// Set campaign properties.
		$this->campaign_stats   = Settings::get_campaign_stats( $campaign_id );
		$this->button_label     = $campaign['button_label'] ?? '';
		$this->welcome_title    = $campaign['modal_title'] ?? '';
		$this->welcome_text     = $campaign['welcome_text'] ?? '';
		$this->campaign_goal    = $campaign['campaign_goal'] ?? '';
		$this->show_progress    = $campaign['show_progress'] ?? '';
		$this->amount_type      = $campaign['amount_type'] ?? '';
		$this->fixed_amounts    = $campaign['fixed_amounts'] ?? '';
		$this->frequency        = $campaign['donation_type'] ?? '';
		$this->address_enabled  = $campaign['address_enabled'] ?? '';
		$this->address_required = $campaign['address_required'] ?? '';
		$this->message_enabled  = $campaign['message_enabled'] ?? '';
	}

	/**
	 * @return mixed|string
	 */
	public function get_button_label() {
		return $this->button_label;
	}

}