<?php
/**
 * React Admin Page.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Localization;

abstract class AbstractReactSubPage extends AbstractAdminPage implements HasCallbackInterface, HasAssetsInterface, SubmenuAdminPageInterface {

	public const SCRIPT_HANDLE      = 'kudos-admin';
	public const STYLE_HANDLE_ADMIN = 'kudos-admin-style';

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_assets(): void {
		// Hide admin notices.
		$this->discard_admin_notices();

		// Let the ThemeProvider control the admin theme colours.
		$this->remove_admin_color_scheme_variables();

		// Enqueue the styles.
		wp_enqueue_style(
			self::STYLE_HANDLE_ADMIN,
			Assets::get_style( 'admin/kudos-admin.css' ) ?? '',
			[ 'wp-components' ],
			KUDOS_VERSION
		);

		// Enqueue the code editor for css.
		$settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

		// Get and enqueue the script.
		$admin_js = Assets::get_script( 'admin/kudos-admin.js' );
		if ( null !== $admin_js ) {
			wp_enqueue_script(
				self::SCRIPT_HANDLE,
				$admin_js['url'],
				$admin_js['dependencies'],
				$admin_js['version'],
				true
			);

			wp_set_script_translations( self::SCRIPT_HANDLE, 'kudos-donations', \dirname( plugin_dir_path( __FILE__ ), 2 ) . '/languages' );

			Localization::add_admin( 'codeEditor', $settings );
			Localization::add_admin( 'demoMode', KUDOS_DEMO_MODE );

			wp_localize_script(
				self::SCRIPT_HANDLE,
				'kudos',
				Localization::get_admin()
			);
		}

		do_action( 'kudos_admin_' . self::SCRIPT_HANDLE . '_enqueued' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function callback(): void {
		echo '<div class="wrap kudos-admin-page">';
		printf(
			'<div id="root" data-title="%1$s" data-view="%2$s"></div>',
			esc_attr( $this->get_page_title() ),
			esc_attr( $this->get_menu_slug() )
		);
		echo '</div>';
	}

	/**
	 * The wp-base-styles stylesheet only redefines the admin theme colour variables on
	 * body.admin-color-*, overriding the values our ThemeProvider sets on <html>. Portalled
	 * UI (modals, popovers) would then use the colour scheme's accent instead of ours.
	 * The handle is kept registered but empty, as other admin styles depend on it.
	 */
	private function remove_admin_color_scheme_variables(): void {
		wp_deregister_style( 'wp-base-styles' );
		wp_register_style( 'wp-base-styles', false, [], KUDOS_VERSION );
	}

	/**
	 * Hide admin notices on our react-based admin pages.
	 */
	private function discard_admin_notices(): void {
		add_action(
			'admin_notices',
			function () {
				ob_start();
			},
			1
		);

		add_action(
			'admin_notices',
			function () {
				ob_end_clean();
			},
			PHP_INT_MAX
		);
	}
}
