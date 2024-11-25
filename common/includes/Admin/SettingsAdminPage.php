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
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style(
			'kudos-admin-style',
			Assets::get_style( 'admin/settings/kudos-admin-settings.css' ),
			[],
			KUDOS_VERSION
		);

		// Get and enqueue the script.
		$admin_js = Assets::get_script( 'admin/settings/kudos-admin-settings.js' );

		wp_enqueue_script(
			'kudos-donations-settings',
			$admin_js['url'],
			$admin_js['dependencies'],
			$admin_js['version'],
			[
				'in_footer' => true,
			]
		);

		$localized_data = apply_filters(
			'kudos_settings_page_localization',
			[
				'migrations_pending' => (bool) get_option( '_kudos_migrations_pending' ),
				'stylesheets'        => [ Assets::get_style( 'admin/kudos-admin-settings.jsx.css' ) ],
			]
		);

		wp_localize_script(
			'kudos-donations-settings',
			'kudos',
			apply_filters( 'kudos_global_localization', $localized_data )
		);
		wp_set_script_translations( 'kudos-donations-settings', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 4;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
