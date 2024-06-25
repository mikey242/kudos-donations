<?php
/**
 * Campaign Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Helper\Assets;

class CampaignAdminPage extends AbstractAdminPage implements HasCallbackInterface, HasAssetsInterface {

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos campaigns', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Campaigns', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_slug(): string {
		return 'kudos-campaigns';
	}

	/**
	 * {@inheritDoc}
	 */
	public function callback(): void {
		echo '<div id="kudos-campaigns" class="kudos-admin-page"></div>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_assets(): void {
		// Enqueue the styles.
		wp_enqueue_style(
			'kudos-donations-settings',
			Assets::get_style( 'admin/kudos-admin-campaigns.jsx.css' ),
			[],
			KUDOS_VERSION
		);

		// Get and enqueue the script.
		$admin_js = Assets::get_script( 'admin/kudos-admin-campaigns.jsx.js' );
		wp_enqueue_script(
			'kudos-donations-settings',
			$admin_js['url'],
			$admin_js['dependencies'],
			$admin_js['version'],
			true
		);

		wp_localize_script(
			'kudos-donations-settings',
			'kudos',
			[
				'version'     => KUDOS_VERSION,
				'stylesheets' => [ Assets::get_style( 'admin/kudos-admin-campaigns.jsx.css' ) . '?ver=KUDOS_VERSION' ],
			]
		);
		wp_set_script_translations(
			'kudos-donations-settings',
			'kudos-donations'
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_position(): ?int {
		return 0;
	}
}
