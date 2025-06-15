<?php
/**
 * React Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;

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
		// Enqueue the styles.
		wp_enqueue_style(
			self::STYLE_HANDLE_ADMIN,
			Assets::get_style( 'admin/kudos-admin.css' ),
			[ 'wp-components' ],
			KUDOS_VERSION
		);

		// Enqueue the code editor for css.
		$settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

		// Get and enqueue the script.
		$admin_js = Assets::get_script( 'admin/kudos-admin.js' );
		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			$admin_js['url'],
			$admin_js['dependencies'],
			$admin_js['version'],
			true
		);

		wp_set_script_translations( self::SCRIPT_HANDLE, 'kudos-donations', \dirname( plugin_dir_path( __FILE__ ), 2 ) . '/languages' );

		$localized_data = apply_filters(
			'kudos_transactions_page_localization',
			[
				'currencies' => Utils::get_currencies(),
				'codeEditor' => $settings,
			]
		);

		wp_localize_script(
			self::SCRIPT_HANDLE,
			'kudos',
			apply_filters(
				'kudos_global_localization',
				$localized_data
			)
		);
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
}
