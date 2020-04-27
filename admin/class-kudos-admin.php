<?php

namespace Kudos;

use Kudos\Mollie\Mollie;
use Kudos\Transactions\Transactions_Table;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Mollie
 * @subpackage Kudos/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kudos-Mollie
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

	/**
	 * Check the Mollie Api key
     *
     * @since    1.0.0
	 */
	public function check_mollie_connection() {

	    parse_str($_REQUEST['formData'], $formData);

	    $mode = $formData['carbon_fields_compact_input']['_kudos_mollie_api_mode'];
	    $apiKey = $formData['carbon_fields_compact_input']['_kudos_mollie_'.$mode.'_api_key'];

		$mollie = new Mollie();

		$result = $mollie->checkApiKey($apiKey);

		if($result) {
			carbon_set_theme_option('mollie_'.$mode.'_api_key', $apiKey);
			carbon_set_theme_option('kudos_mollie_api_mode', $mode);
			wp_send_json_success("Connection successful!");
		} else {
            wp_send_json_error("Error connecting with Mollie, please check the ". $mode ." API key and try again");
		}
    }

	/**
	 * Creates the transactions admin page
     *
	 * @since    1.0.0
	 */
	public function create_transaction_page() {
		add_submenu_page(
			'crb_carbon_fields_container_kudos.php',
			'Kudos Transacties',
			'Transacties',
			'manage_options',
			'kudos-transactions',
			[$this, 'transactions_table']

		);
    }

	/**
	 * Creates the transactions table
     *
	 * @since    1.0.0
	 */
	public function transactions_table() {
	    $table = new Transactions_Table();
	    $table->prepare_items();
	    ?>
	    <div class="wrap">
		    <div id="icon-users" class="icon32"></div>
		    <h2>Transacties</h2>
		    <?php $table->display(); ?>
	    </div>
	    <?php
    }

	/**
	 * Creates and registers the [kudos] shortcode
     *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'kudos', function ( $atts ) {

			$atts = shortcode_atts(
				[
					'label' => '',
					'text'  => ''
				],
				$atts,
				'kudos'
			);

			return kudos_button( $atts['label'], $atts['text'], false );
		} );
	}

}
