<?php

namespace Kudos;

use Kudos\Mollie\Mollie;
use Kudos\Mollie\Webhook;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos
 * @subpackage Kudos/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Kudos
 * @subpackage Kudos/public
 * @author     Michael Iseard <michael@iseard.media>
 */
class Kudos_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../dist/css/kudos-public.css', [], $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . 'blocks', plugin_dir_url( __FILE__ ) . '../dist/css/kudos-blocks.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../dist/js/kudos-public.js', [ 'jquery' ], $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-blocks', plugin_dir_url( __FILE__ ) . '../dist/js/kudos-blocks.js', [ 'jquery' ], $this->version, false );
		wp_localize_script( $this->plugin_name, 'wp_ajax', ['ajaxurl' => admin_url('admin-ajax.php')]);

	}

	public function create_payment() {
		parse_str($_REQUEST['form'], $form);
		$value = $form['value'];
		$email = $form['email_address'];
		$redirectUrl = $_REQUEST['redirectUrl'];

		$mollie = new Mollie();
		$payment = $mollie->payment($value, $email, $redirectUrl);
		wp_send_json_success($payment->getCheckoutUrl());
	}

	/**
	 * Register custom query vars
	 *
	 * @param array $vars The array of available query variables
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/query_vars
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'kudos_order_id';
		return $vars;
	}

	public function register_webhook() {
		$webhook = new Webhook();
		$webhook->register_webhook();
	}

	/**
	 * @return bool | string
	 */
	public function check_transaction() {
		$order_id = base64_decode($_REQUEST['order_id']);

		if(!$order_id) {
			return false;
		}

		$transaction = new Transactions\Transaction();
		$transaction = $transaction->get_transaction($order_id);
		$order_id_session = $_COOKIE['order_id'];

		if($order_id === $order_id_session) {
			// Unset cookie to prevent repeat message
			setcookie('order_id', '', 1);
			wp_send_json_success($transaction);
		}

		return false;
	}

}
