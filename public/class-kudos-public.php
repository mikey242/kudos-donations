<?php

namespace Kudos;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Donations-Mollie
 * @subpackage Kudos/public
 */

/**
 * The public-facing functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Kudos-Donations-Mollie
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
	 * @var Kudos_Logger
	 *
	 * @since    1.0.0
	 */
	private $logger;

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
		$this->logger = new Kudos_Logger();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . '-public', get_asset_path('kudos-public.css'), [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name . '-public', get_asset_path('kudos-public.js'), [ 'jquery' ], $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-vendors', get_asset_path('vendors.js'), [ 'jquery' ], $this->version, false );
		wp_localize_script( $this->plugin_name . '-public', 'kudos', [
		        'ajaxurl' => admin_url('admin-ajax.php'),
                'name_required' => __('Your name is required', 'kudos-donations'),
                'email_required' => __('Your email is required', 'kudos-donations'),
                'email_invalid' => __('Please enter a valid email', 'kudos-donations'),
                'value_required' => __('Donation amount is required', 'kudos-donations'),
                'value_minimum' => __('Minimum donation is 1 euro', 'kudos-donations'),
                'value_digits' => __('Only digits are valid', 'kudos-donations')
        ]);

	}

	/**
	 * Register the assets used for blocks.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_block_assets() {

		wp_enqueue_style( $this->plugin_name . '-blocks', get_asset_path('kudos-blocks.css'), [], $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name . '-blocks', get_asset_path('kudos-blocks.js'), [ 'jquery' ], $this->version, false );

	}

	/**
	 * Creates a payment with Mollie.
	 *
	 * @since    1.0.0
	 */
	public function create_payment() {
		parse_str($_REQUEST['form'], $form);
		if(!wp_verify_nonce($form['_wpnonce'], 'kudos_submit')) {
			$this->logger->log('wp_verify_nonce failed', 'CRITICAL');
			wp_send_json_error(['message' => __('Request invalid.', 'kudos-donations')]);
		}
		$value = $form['value'];
		$name = $form['name'];
		$email = $form['email_address'];
		$redirectUrl = $_REQUEST['redirectUrl'];

		$mollie = new Kudos_Mollie();
		$payment = $mollie->payment($value, $redirectUrl, $name, $email);
		if($payment) {
			wp_send_json_success($payment->getCheckoutUrl());
		}

		wp_send_json_error(['message' => __('Error creating Mollie payment. Please try again later.', 'kudos-donations')]);
	}

	/**
	 * Registers the webhook url
     *
     * @since      1.0.0
	 * @return void
	 */
	public function register_webhook() {
		$mollie = new Kudos_Mollie();
		$mollie->register_webhook();
	}

	/**
	 * Using the ajax provided $_REQUEST variable checks payment status
	 *
	 * @since    1.0.0
	 * @return bool | string
	 */
	public function check_transaction() {

		$order_id = $_REQUEST['order_id'] ? base64_decode($_REQUEST['order_id']) : null;
		$order_id_session = !empty($_COOKIE['kudos_order_id']) ? $_COOKIE['kudos_order_id'] : null;

		// If either $_GET['order_id'] or $_COOKIE['kudos_order_id'] not set then stop
		if(!$order_id || !$order_id_session) {
			return false;
		}

		if($order_id === $order_id_session) {

			$transaction = new Transactions\Transaction();
			$modal = new Kudos_Modal();
			$transaction = $transaction->get_transaction($order_id, ['status', 'value', 'name']);

			switch($transaction->status) {
				case 'paid':
					$vars = [
						'{{value}}' => number_format_i18n($transaction->value, 2),
						'{{name}}' => $transaction->name
					];
					$header = strtr(carbon_get_theme_option('kudos_return_message_header'), $vars);
					$text = strtr(carbon_get_theme_option('kudos_return_message_text'), $vars);
					$return['html'] = $modal->get_message_modal($header, $text);
					break;
				case 'canceled':
					$header = __('Payment cancelled', 'kudos-donations');
					$return['html'] = $modal->get_message_modal($header);
	                break;
                default:
	                $return['html'] = false;
			}

			// Unset cookie to prevent repeat message
//			setcookie('kudos_order_id', '', 1);
			wp_send_json_success($return);
		}

		return false;
	}

	/**
	 * Gets url Mollie will use to redirect customer to after payment complete
	 *
	 * @since    1.0.0
	 * @return string|void
	 */
	public static function get_return_url() {
		$use_custom = carbon_get_theme_option('kudos_custom_return_enable');
		$custom_url = get_option('_kudos_custom_return_url');
		if($use_custom && $custom_url) {
			return $custom_url;
		} else {
			$redirectUrl = is_ssl() ? 'https://' : 'http://';
			$redirectUrl .= $_SERVER['HTTP_HOST'] . parse_url( $_SERVER["REQUEST_URI"], PHP_URL_PATH );
			return $redirectUrl;
		}
	}

	/**
	 * Checks if required settings are saved before displaying button or modal
	 *
	 * @since    1.0.0
	 * @return bool
	 */
	public static function ready() {
		$apiMode = carbon_get_theme_option('kudos_mollie_api_mode');
		$apiKey = carbon_get_theme_option('kudos_mollie_'.$apiMode.'_api_key');
		if($apiKey) {
			return true;
		}
		return false;
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
					'button-label' => '',
					'form-header' => '',
					'form-text'  => ''
				],
				$atts,
				'kudos'
			);

			$button = new Kudos_Button($atts);
			return $button->get_button(false);
		} );
	}

	/**
	 * Checks if the kudos shortcode or block exists on the page and places the kudos modal
     *
     * @since    1.0.0
	 */
	public function place_payment_modal() {

	    global $post;

		if(has_block('carbon-fields/kudos-button') || (is_object($post) ? has_shortcode($post->post_content, 'kudos') : null)) {
			$modal = new Kudos_Modal();
			echo $modal->get_payment_modal();
		}
	}

}
