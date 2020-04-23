<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos
 * @subpackage Kudos/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kudos
 * @subpackage Kudos/admin
 * @author     Michael Iseard <michael@iseard.media>
 */
class Kudos_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../dist/css/kudos-admin.css', [], $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . 'blocks', plugin_dir_url( __FILE__ ) . '../dist/css/kudos-blocks.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../dist/js/kudos-admin.js', [ 'jquery' ], $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-blocks', plugin_dir_url( __FILE__ ) . '../dist/js/kudos-blocks.js', [ 'jquery' ], $this->version, false );
		wp_localize_script( $this->plugin_name, 'wp_ajax', ['ajaxurl' => admin_url('admin-ajax.php')]);

	}

	public function kudos_admin_init() {
		Kudos_Carbon::init();
	}

	/**
	 * Check the Mollie Api key
	 */
	public function check_mollie_connection() {
		$apiKey = sanitize_text_field($_REQUEST['apiKey']);
		$mollie = new Mollie();
		$result = $mollie->checkApiKey($apiKey);
		if($result) {
			carbon_set_theme_option('mollie_api_key', $apiKey);
		    $return = "Connection successful!";
		} else {
            $return = "Error connecting with Mollie, please check the API key and try again";
		}
        wp_send_json_success($return);
    }

}
