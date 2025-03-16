<?php
/**
 * Settings Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Helper\Assets;

class SettingsAdminPage extends AbstractAdminPage implements HasCallbackInterface, HasAssetsInterface, SubmenuAdminPageInterface {

	public const SCRIPT_HANDLE_SETTINGS = 'kudos-donations-settings';
	public const STYLE_HANDLE_ADMIN     = 'kudos-admin-style';

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos settings', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Settings', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'kudos-settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function callback(): void {
		echo '<div id="kudos-settings" class="kudos-admin-page"></div>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_assets(): void {
		// Enqueue the styles.
		wp_enqueue_style(
			self::STYLE_HANDLE_ADMIN,
			Assets::get_style( 'admin/settings/kudos-admin-settings.css' ),
			[ 'wp-components' ],
			KUDOS_VERSION
		);

		// Get and enqueue the script.
		$admin_js = Assets::get_script( 'admin/settings/kudos-admin-settings.js' );

		wp_enqueue_script(
			self::SCRIPT_HANDLE_SETTINGS,
			$admin_js['url'],
			$admin_js['dependencies'],
			$admin_js['version'],
			true
		);

		$localized_data = apply_filters(
			'kudos_settings_page_localization',
			[
				'migrations_pending' => (bool) get_option( '_kudos_migrations_pending' ),
			]
		);

		wp_localize_script(
			self::SCRIPT_HANDLE_SETTINGS,
			'kudos',
			apply_filters( 'kudos_global_localization', $localized_data )
		);
		wp_set_script_translations( self::SCRIPT_HANDLE_SETTINGS, 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 6;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
