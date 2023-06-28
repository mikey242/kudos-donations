<?php

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Helpers\Assets;
use IseardMedia\Kudos\Helpers\Settings;

class SettingsAdminPage extends AbstractAdminPage {

	public function get_page_title(): string {
		return __('Kudos settings', 'kudos-donations');
	}

	public function get_menu_title(): string {
		return __('Settings', 'kudos-donations');
	}

	public function get_menu_slug(): string {
		return 'kudos-settings';
	}

	public function callback(): void {
		echo '<div id="kudos-settings"></div>';
	}

	public function register_assets(): void {
		// Enqueue the styles
		wp_enqueue_style(
			'kudos-donations-settings',
			Assets::get_style('admin/kudos-admin-settings.jsx.css'),
			[],
			KUDOS_VERSION
		);

		// Get and enqueue the script
		$admin_js = Assets::get_script('admin/kudos-admin-settings.jsx.js');
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
				'version'            => KUDOS_VERSION,
				'migrations_pending' => (bool)Settings::get_setting('migrations_pending'),
				'stylesheets'        => [Assets::get_style('admin/kudos-admin-settings.jsx.css')],
			]
		);
		wp_set_script_translations('kudos-donations-settings', 'kudos-donations');
	}
}