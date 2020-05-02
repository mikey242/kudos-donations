<?php

namespace Kudos;

use Kudos\Mollie\Mollie;

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
		$value = $form['value'];
		$name = $form['name'];
		$email = $form['email_address'];
		$redirectUrl = $_REQUEST['redirectUrl'];

		$mollie = new Mollie();
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
		$mollie = new Mollie();
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
		$order_id_session = $_COOKIE['kudos_order_id'];

		// If either $_GET['kudos_order_id'] or $_COOKIE['kudos_order_id'] not set then stop
		if(!$order_id || !$order_id_session) {
			return false;
		}

		if($order_id === $order_id_session) {

			$transaction = new Transactions\Transaction();
			$transaction = $transaction->get_transaction($order_id, ['status', 'value', 'name']);
			$return['trigger'] = true;

			switch($transaction->status) {
				case 'paid':
					$vars = [
						'{{value}}' => number_format_i18n($transaction->value, 2),
						'{{name}}' => $transaction->name
					];
					$return['modal_header'] = strtr(carbon_get_theme_option('kudos_return_message_header'), $vars);
					$return['modal_text'] = strtr(carbon_get_theme_option('kudos_return_message_text'), $vars);
					break;
				case 'canceled':
					$return['modal_header'] = __('Geannuleerd', 'kudos');
	                $return['modal_text'] = __('Betaling geannuleerd', 'kudos');
	                break;
                default:
	                $return['trigger'] = false;
			}

			// Unset cookie to prevent repeat message
			setcookie('kudos_order_id', '', 1);
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
	 * Checks if required settings are saved before displaying button
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
	 * Checks if the kudos shortcode or block exists on the page and places the kudos modal
     *
     * @since    1.0.0
	 */
	public function place_modal() {

	    global $post;

		if(has_block('carbon-fields/kudos-button') || has_shortcode($post->post_content, 'kudos')) {

		    $text = "Wat lief dat je ons wilt steunen. Doneer eenmalig zonder verplichtingen.";
			$header = "Steun ons!";

			?>
			<div id="kudos_form_modal" class="kudos_modal hidden" aria-hidden="true">
				<div class="kudos_modal_overlay" tabindex="-1" data-micromodal-close>
					<div class="kudos_modal_container" role="dialog" aria-modal="true" aria-labelledby="kudos_modal-title">
                        <header class="kudos_modal_header">
                            <div class="kudos_modal_logo"></div>
                            <button class="kudos_modal_close" aria-hidden="true" aria-label="Close modal" data-micromodal-close></button>
                        </header>
                        <div id="kudos_modal_content" class="kudos_modal_content mt-4">
                            <div class="text-center">
                                <h2 id="kudos_modal_title" class="font-normal"><?php echo $header ?></h2>
                                <p id="kudos_modal_text"><?php echo $text ?></p>
                                <p class="text-red-500 kudos_error_message"></p>
                            </div>
                            <form id="kudos_form" action="">
                                <input type="text" name="name" placeholder="<?php _e('Name', 'kudos-donations')?>" />
                                <input type="email" class="mt-3" name="email_address" placeholder="<?php _e('E-mail address', 'kudos-donations')?>" />
                                <?php /* translators: %s: Star denoting required field */ ?>
                                <input required type="text" min="1" class="mt-3" name="value" placeholder="<?php printf(__('Amount %s', 'kudos-donations'), '*') ?>" />
                                <div class="payment_by mt-3 text-muted text-right">
                                    <small class="text-gray-600">
                                        <span class="fa-stack fa-xs align-middle">
                                            <i class="fas fa-circle fa-stack-2x"></i>
                                            <i class="fas fa-lock fa-stack-1x fa-inverse"></i>
                                        </span>
                                        <?php _e('Secure payment by', 'kudos-donations') ?>
                                    </small>
                                </div>
                                <footer class="kudos_modal_footer text-center">
                                    <button class="kudos_btn kudos_btn_primary_outline mr-3" type="button" data-micromodal-close aria-label="<?php _e('Cancel', 'kudos-donations') ?>"><?php _e('Cancel', 'kudos-donations') ?></button>
                                    <button id="kudos_submit" class="kudos_btn kudos_btn_primary" type="submit" aria-label="<?php _e('Donate', 'kudos-donations') ?>"><?php _e('Donate', 'kudos-donations') ?></button>
                                </footer>
                            </form>
                            <i class="kudos_spinner"></i>
                        <div>
					</div>
				</div>
			</div>
			<?php
		}
	}

}
