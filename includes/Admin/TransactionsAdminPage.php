<?php
/**
 * Transactions Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;

class TransactionsAdminPage extends AbstractAdminPage implements HasCallbackInterface, HasAssetsInterface, SubmenuAdminPageInterface {

	public const SCRIPT_HANDLE_CAMPAIGNS = 'kudos-donations-transactions';

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Transactions', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'kudos-transactions';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 3;
	}

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
			SettingsAdminPage::STYLE_HANDLE_ADMIN,
			Assets::get_style( 'admin/settings/kudos-admin-settings.css' ),
			[ 'wp-components' ],
			KUDOS_VERSION
		);

		// Enqueue the code editor for css.
		$settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

		// Get and enqueue the script.
		$admin_js = Assets::get_script( 'admin/campaigns/kudos-admin-transactions.js' );
		wp_enqueue_script(
			self::SCRIPT_HANDLE_CAMPAIGNS,
			$admin_js['url'],
			$admin_js['dependencies'],
			$admin_js['version'],
			true
		);

		wp_set_script_translations( self::SCRIPT_HANDLE_CAMPAIGNS, 'kudos-donations', \dirname( plugin_dir_path( __FILE__ ), 2 ) . '/languages' );

		$localized_data = apply_filters(
			'kudos_transactions_page_localization',
			[
				'currencies' => Utils::get_currencies(),
				'codeEditor' => $settings,
			]
		);

		wp_localize_script(
			self::SCRIPT_HANDLE_CAMPAIGNS,
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
		printf( '<div id="root" data-title="%s"></div>', esc_attr( $this->get_page_title() ) );
		echo '</div>';
	}
}
