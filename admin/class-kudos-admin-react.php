<?php

namespace Kudos;

class Kudos_Admin_React {

	function codeinwp_options_assets() {
		wp_enqueue_script( 'kudos-donations-admin-react', get_asset_url('kudos-admin-react.js'), [ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element' ], KUDOS_VERSION, true );
		wp_enqueue_style( 'kudos-donations-admin-react', get_asset_url('kudos-admin-react.css'), [ 'wp-components' ], KUDOS_VERSION,'all' );
	}

	function kudos_menu_callback() {
		echo '<div id="codeinwp-awesome-plugin"></div>';
	}

	function codeinwp_add_option_menu() {
		$page_hook_suffix = add_options_page(
			__( 'Kudos Donations', 'textdomain' ),
			__( 'Kudos Donations', 'textdomain' ),
			'manage_options',
			'kudos-donations',
			[$this, 'kudos_menu_callback']
		);

		add_action( "admin_print_scripts-{$page_hook_suffix}", [$this, 'codeinwp_options_assets'] );
	}

	function codeinwp_register_settings() {

		register_setting(
			'kudos_donations',
			'kd_mollie_api_mode',
			[
				'type'         => 'string',
				'show_in_rest' => true,
				'default'      => 'test',
			]
		);

		register_setting(
			'kudos_donations',
			'kd_mollie_test_key',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		register_setting(
			'kudos_donations',
			'kd_mollie_live_key',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);
	}

}

$admin_react = new Kudos_Admin_React();

add_action('admin_menu', [$admin_react, 'codeinwp_add_option_menu']);
add_action( 'init', [$admin_react, 'codeinwp_register_settings'] );