<?php

namespace Kudos\Helpers;

class Campaigns {

	/**
	 * @var mixed
	 */
	private $campaigns;

	public function __construct() {

		$this->campaigns = Settings::get_setting('campaigns');

	}

	/**
	 * Adds default campaign is no campaigns found
	 *
	 * @since 2.3.0
	 */
	public function add_default() {

		$default_campaign[0] = [
			'id' => 'default',
			'name' => 'Default',
			'modal_title' => 'Hello',
			'welcome_text' => 'Welcome text',
			'address_required' => true,
			'amount_type'   => 'both',
			'fixed_amounts' => '1,5,20,50',
			'donation_type'    => 'both',
			'protected'      => true
		];

		if(empty($this->campaigns)) update_option(Settings::PREFIX . 'campaigns', $default_campaign);

	}

	/**
	 * Gets the campaign by specified column (e.g slug)
	 *
	 * @param string $value
	 *
	 * @return array|null
	 * @since 2.3.0
	 */
	public function get_campaign( string $value ): ?array {

		$campaigns = $this->campaigns;
		$key = array_search($value, array_column($campaigns, 'id'));

		// Check if key is an index and if so return index from forms
		if(is_int($key)) {
			return $campaigns[$key];
		}

		return null;

	}

	/**
	 * Returns all campaigns
	 *
	 * @return null|array
	 * @since 2.3.0
	 */
	public function get_all(): ?array {

		return (array) $this->campaigns;

	}

	/**
	 * Sanitize the various setting fields in the donation form array
	 *
	 * @param $campaigns
	 *
	 * @return array
	 * @since 2.3.0
	 */
	public static function sanitize_campaigns($campaigns): array {

		//Define the array for the updated options
		$output = [];

		// Loop through each of the options sanitizing the data
		foreach ($campaigns as $key=>$form) {

			if(!array_search('id', $form)) $output[$key]['id'] = strtoupper(uniqid('kc_'));

			foreach ($form as $option=>$value) {

				switch ($option) {
					case 'modal_title':
					case 'welcome_text':
						$output[$key][$option] = sanitize_text_field($value);
						break;
					case 'amount_type':
					case 'donation_type':
						$output[$key][$option] = sanitize_key($value);
						break;
					default:
						$output[$key][$option] = $value;
				}
			}
		}

		return $output;
	}

}
