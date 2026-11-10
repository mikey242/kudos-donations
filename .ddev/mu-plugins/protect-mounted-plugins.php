<?php
/**
 * Plugin Name: Protect mounted plugins (DDEV)
 * Description: Kudos Donations and Kudos Donations Plus are git checkouts mounted into this development site. Updating or replacing them through WordPress would delete the checkout (including .git) and unpack a release zip in its place, so this hides their updates and refuses such installs.
 *
 * Copied into wp-content/mu-plugins by .ddev/scripts/setup-wordpress.sh.
 */

const KUDOS_DDEV_MOUNTED_PLUGINS = [
	'kudos-donations/kudos-donations.php',
	'kudos-donations-plus/kudos-donations-plus.php',
];

// Don't offer updates for the mounted plugins.
add_filter(
	'site_transient_update_plugins',
	static function ( $transient ) {
		if ( is_object( $transient ) && isset( $transient->response ) && is_array( $transient->response ) ) {
			foreach ( KUDOS_DDEV_MOUNTED_PLUGINS as $plugin_file ) {
				unset( $transient->response[ $plugin_file ] );
			}
		}

		return $transient;
	}
);

// Never auto-update them.
add_filter(
	'auto_update_plugin',
	static function ( $update, $item ) {
		return in_array( $item->plugin ?? '', KUDOS_DDEV_MOUNTED_PLUGINS, true ) ? false : $update;
	},
	10,
	2
);

// Refuse any install over them: updates, and replacing a plugin by uploading a zip.
add_filter(
	'upgrader_pre_install',
	static function ( $response, $hook_extra ) {
		$targets = array_merge( [ $hook_extra['plugin'] ?? '' ], (array) ( $hook_extra['plugins'] ?? [] ) );

		if ( array_intersect( $targets, KUDOS_DDEV_MOUNTED_PLUGINS ) ) {
			return new WP_Error( 'kudos_ddev_mounted', 'This plugin is a mounted git checkout in this DDEV site; update it with git instead.' );
		}

		return $response;
	},
	10,
	2
);
