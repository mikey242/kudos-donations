<?php
/**
 * Constants for Kudos Donations.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

// Stub constants expected by the plugin.
define( 'KUDOS_VERSION', '4.2.0' );
define( 'KUDOS_DB_VERSION', '4.2.0' );
define( 'KUDOS_PLUGIN_FILE', __DIR__ . '/kudos-donations.php' );
define( 'KUDOS_PLUGIN_DIR', __DIR__ . '/' );
define( 'KUDOS_PLUGIN_URL', 'http://localhost/wp-content/plugins/kudos-donations/' );
define( 'KUDOS_CACHE_DIR', sys_get_temp_dir() . '/kudos-donations/' );
define( 'KUDOS_STORAGE_DIR', sys_get_temp_dir() . '/kudos-donations/storage/' );
define( 'KUDOS_DEBUG', false );
define( 'KUDOS_APP_ENV', 'development' );
define( 'KUDOS_ENV_IS_DEVELOPMENT', true );
