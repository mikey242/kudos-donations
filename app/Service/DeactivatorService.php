<?php

namespace Kudos\Service;

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 */
class DeactivatorService {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		LoggerService::factory()->info( 'Kudos Donations plugin deactivated' );

	}

}
