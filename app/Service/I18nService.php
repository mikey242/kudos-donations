<?php

namespace Kudos\Service;

class I18nService extends AbstractService {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		// Relative location of translations.
		$path = dirname( plugin_basename( __FILE__ ), 3 ) . '/languages';

		// Load translations.
		load_plugin_textdomain(
			'kudos-donations',
			false,
			$path
		);

	}

}
