<?php

namespace Kudos;

class Kudos_Admin_React {

	function kudos_options_assets() {
		wp_enqueue_script( 'kudos-donations-admin-react', get_asset_url('kudos-admin-react.js'), [ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-data' ], KUDOS_VERSION, true );
		wp_localize_script('kudos-donations-admin-react', 'kudos', [
			'version' => KUDOS_VERSION,
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'checkApiUrl' => rest_url('kudos/v1/mollie/admin'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		]);
		wp_enqueue_style( 'kudos-donations-admin-react', get_asset_url('kudos-admin-react.css'), [ 'wp-components' ], KUDOS_VERSION,'all' );
	}

	function kudos_menu_callback() {
		echo '<div id="kudos-dashboard"></div>';
	}

	function kudos_add_option_menu() {
		$page_hook_suffix = add_options_page(
			__( 'Kudos Donations', 'textdomain' ),
			__( 'Kudos Donations', 'textdomain' ),
			'manage_options',
			'kudos-donations',
			[$this, 'kudos_menu_callback']
		);

		add_action( "admin_print_scripts-{$page_hook_suffix}", [$this, 'kudos_options_assets'] );
	}

	function kudos_donations_register_settings() {

		// Mollie Settings

		register_setting(
			'kudos_donations',
			'_kudos_mollie_connected',
			[
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_mollie_api_mode',
			[
				'type'         => 'string',
				'show_in_rest' => true,
				'default'      => 'test',
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_mollie_test_api_key',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_mollie_live_api_key',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		// Email Settings

		register_setting(
			'kudos_donations',
			'_kudos_smtp_enable',
			[
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_smtp_host',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_smtp_encryption',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_smtp_autotls',
			[
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => true,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_smtp_username',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_smtp_password',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_smtp_port',
			[
				'type'         => 'string',
				'show_in_rest' => true,
			]
		);
	}

}

$admin_react = new Kudos_Admin_React();

add_action('admin_menu', [$admin_react, 'kudos_add_option_menu']);
add_action( 'init', [$admin_react, 'kudos_donations_register_settings'] );