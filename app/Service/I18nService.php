<?php

namespace Kudos\Service;

class I18nService extends AbstractService {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		// Relative location of translations
		$path = dirname( plugin_basename( __FILE__ ), 3 ) . '/languages';

		// Load translations
		$result = load_plugin_textdomain(
			'kudos-donations',
			false,
			$path
		);

		// Log debug message if no local translation found
		if(!$result) {
			$this->logger->debug('Could not load plugin textdomain!', ['domain' => get_locale(),'path' => $path]);
		}

	}



}