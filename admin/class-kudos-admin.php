<?php

namespace Kudos;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kudos-Donations
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

		wp_enqueue_style( $this->plugin_name . '-admin', get_asset_path('kudos-admin.css'), [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name . '-admin', get_asset_path('kudos-admin.js'), [ 'jquery' ], $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-vendors', get_asset_path('vendors.js'), [ 'jquery' ], $this->version, false );
		wp_localize_script( $this->plugin_name . '-admin', 'kudos', ['ajaxurl' => admin_url('admin-ajax.php')]);

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

		$mollie = new Kudos_Mollie();

		$result = $mollie->checkApiKey($apiKey);

		if($result) {
			carbon_set_theme_option('kudos_mollie_'.$mode.'_api_key', $apiKey);
			carbon_set_theme_option('kudos_mollie_api_mode', $mode);
			/* translators: %s: API mode */
			wp_send_json_success(sprintf(__("%s API key connection was successful!", 'kudos-donations'), ucfirst($mode)));
		} else {
			/* translators: %s: API mode */
            wp_send_json_error( sprintf(__("Error connecting with Mollie, please check the %s API key and try again.", 'kudos-donations'), ucfirst($mode)));
		}
    }

	/**
	 * Creates the transactions admin page
     *
	 * @since    1.0.0
	 */
	public function create_transaction_page() {
		add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Transactions', 'kudos-donations'), 'Kudos'),
			__('Transactions', 'kudos-donations'),
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
		$message = '';
		if ('delete' === $table->current_action()) {
			$message = '<div class="updated below-h2" id="message"><p>' . __('Transaction deleted', 'kudos-donations') . '</p></div>';
		} elseif ('bulk-delete' === $table->current_action() && isset($_REQUEST['bulk-delete'])) {
			/* translators: %s: Number of transactions */
			$message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('%s transaction(s) deleted', 'kudos-donations'), count($_REQUEST['bulk-delete'])) . '</p></div>';
        }
	    ?>
	    <div class="wrap">
		    <h2><?php _e('Transactions', 'kudos-donations'); ?></h2>
            <p><?php _e("Your recent Kudos transactions",'kudos-donations');?></p>
		    <?php echo $message; ?>
            <form id="transactions-table" method="POST">
            <?php
                $table->display();
            ?>
            </form>
	    </div>
	    <?php
    }

}
