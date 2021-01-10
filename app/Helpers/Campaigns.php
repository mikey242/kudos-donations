<?php

namespace Kudos\Helpers;

class Campaigns {

	/**
	 * Adds default campaign
	 *
	 * @since 2.3.0
	 */
	public static function add_default() {

		$default_campaign[0] = [
			'id' => 'default',
			'slug' => 'default',
			'name' => 'Default',
			'modal_title' => 'Hello',
			'welcome_text' => 'Welcome text',
			'address_required' => true,
			'amount_type'   => 'both',
			'fixed_amounts' => '1,5,20,50',
			'donation_type'    => 'both',
			'protected'      => true
		];

		if(empty(Settings::get_setting('campaigns'))) update_option(Settings::PREFIX . 'campaigns', $default_campaign);

	}

	/**
	 * Gets the campaign by specified column (e.g slug)
	 *
	 * @param string $value
	 * @param string $column
	 *
	 * @return array|null
	 * @since 2.3.0
	 */
	public static function get_campaign( string $value, $column = 'slug' ): ?array {

		$forms = Settings::get_setting('campaigns');
		$key = array_search($value, array_column($forms, $column));

		// Check if key is an index and if so return index from forms
		if(is_int($key)) {
			return $forms[$key];
		}

		return null;

	}

}
