<?php

namespace Kudos;

class Kudos_Admin_React {

	function kudos_options_assets() {
		wp_enqueue_script( 'kudos-donations-admin-react', get_asset_url('kudos-admin-react.js'), [ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-data' ], KUDOS_VERSION, true );
		wp_localize_script('kudos-donations-admin-react', 'kudos', [
			'version' => KUDOS_VERSION,
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'checkApiUrl' => rest_url('kudos/v1/mollie/admin'),
			'sendTestUrl' => rest_url('kudos/v1/email/test'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		]);
		wp_enqueue_style( 'kudos-donations-admin-react', get_asset_url('kudos-admin-react.css'), [ 'wp-components' ], KUDOS_VERSION,'all' );
	}

	function kudos_menu_callback() {
		echo '<div id="kudos-dashboard"></div>';
	}

	function kudos_add_menu_page() {
		$page_hook_suffix = add_menu_page(
			__( 'Kudos Donations', 'textdomain' ),
			__( 'Kudos Donations', 'textdomain' ),
			'manage_options',
			'kudos-settings',
			[$this, 'kudos_menu_callback'],
			'data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>
            ')
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
			'_kudos_email_receipt_enable',
			[
				'type'         => 'boolean',
				'show_in_rest' => true,
				'default'      => false,
			]
		);

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

		// Donation button settings

		register_setting(
			'kudos_donations',
			'_kudos_button_label',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Donate now', 'kudos-donations')
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_button_style',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => 'style-orange'
			]
		);

		// Donation form

		register_setting(
			'kudos_donations',
			'_kudos_name_required',
			[
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_email_required',
			[
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_form_header',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Support us!', 'kudos-donations')
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_form_text',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Thank you for your donation. We appreciate your support!', 'kudos-donations')
			]
		);

		// Completed payment settings

		register_setting(
			'kudos_donations',
			'_kudos_return_message_enable',
			[
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => true
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_return_message_header',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => __('Thank you!', 'kudos-donations')
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_return_message_text',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
				'default'       => sprintf(__('Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations'), '{{value}}')
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_custom_return_enable',
			[
				'type'          => 'boolean',
				'show_in_rest'  => true,
				'default'       => false
			]
		);

		register_setting(
			'kudos_donations',
			'_kudos_custom_return_url',
			[
				'type'          => 'string',
				'show_in_rest'  => true,
			]
		);
	}

}

$admin_react = new Kudos_Admin_React();

add_action('admin_menu', [$admin_react, 'kudos_add_menu_page']);
add_action( 'init', [$admin_react, 'kudos_donations_register_settings'] );