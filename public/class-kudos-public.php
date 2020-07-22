<?php

namespace Kudos;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since   1.0.0
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
	 * @since   1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since   1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @var Kudos_Logger
	 *
	 * @since   1.0.0
	 */
	private $logger;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
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
	 * @since   1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . '-public', get_asset_url('kudos-public.css'), [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {

		$handle = $this->plugin_name . '-public';

		wp_enqueue_script( 'micromodal', plugin_dir_url( __FILE__ ) . '../dist/js/vendor/micromodal.min.js', [], '0.4.6', true );
		wp_enqueue_script( 'jquery-validate', plugin_dir_url( __FILE__ ) . '../dist/js/vendor/jquery.validate.min.js', [ 'jquery' ], '1.19.1', true );
		wp_enqueue_script( $handle, get_asset_url('kudos-public.js'), ['jquery', 'micromodal', 'jquery-validate'], $this->version, true );
		wp_localize_script( $handle, 'kudos', [
		        'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_DIR . 'languages');
	}

	/**
	 * Register the assets used for blocks.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_block_assets() {

		$handle = $this->plugin_name . '-button-block';

		wp_enqueue_style( $handle, get_asset_url('kudos-button-block.css'), [], $this->version, 'all' );
		wp_enqueue_script($handle, get_asset_url('kudos-button-block.js'), [ 'wp-i18n', 'wp-edit-post', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-api' ], $this->version, true );
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_DIR . 'languages');
	}

	/**
	 * Creates a payment with Mollie.
	 *
	 * @since   1.0.0
	 */
	public function create_payment() {

		parse_str($_REQUEST['form'], $form);
		if(!wp_verify_nonce($form['_wpnonce'], 'kudos_submit')) {
			$this->logger->log('wp_verify_nonce failed', 'CRITICAL');
			wp_send_json_error(['message' => __('Request invalid.', 'kudos-donations')]);
		}

		// Sanitize form fields
		$value = intval($form['value']);
		$payment_frequency = (isset($form['recurring_frequency']) ? sanitize_text_field($form['recurring_frequency']) : 'oneoff');
		$recurring_length = (isset($form['recurring_length']) ? intval($form['recurring_length']) : 0);
		$name = sanitize_text_field($form['name']);
		$email = sanitize_email($form['email_address']);
		$street = sanitize_text_field($form['street']);
		$postcode = sanitize_text_field($form['postcode']);
		$city = sanitize_text_field($form['city']);
		$country = sanitize_text_field($form['country']);
		$redirectUrl = sanitize_text_field($form['return_url']);
		$buttonName = sanitize_text_field($form['donation_label']);
		$customerId = null;

		$mollie = new Kudos_Mollie();

		// Get or create donor and fetch their Mollie customer id
		if($email) {
			$donorClass = new Kudos_Donor();
			$donor = $donorClass->get_by(['email' => $email]);
			if($donor) {
				$donorClass->update_donor($email, [
					'name' => $name,
					'street' => $street,
					'postcode' => $postcode,
					'city' => $city,
					'country' => $country
				]);
			} else {
				$customer = $mollie->create_customer($email, $name);
				$donorClass->insert_donor($email, $customer->id, $name, $street, $postcode, $city);
				$donor = $donorClass->get_by(['email' => $email]);
			}
			$customerId = $donor->customer_id;
		}

		$payment = $mollie->create_payment($value, $payment_frequency, $recurring_length, $redirectUrl, $buttonName, $name, $email, $customerId);
		if($payment) {
			wp_send_json_success($payment->getCheckoutUrl());
		}

		wp_send_json_error(['message' => __('Error creating Mollie payment. Please try again later.', 'kudos-donations')]);
	}

	/**
	 * Registers the webhook url
     *
     * @since   1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Mollie webhook
		$mollie = new Kudos_Mollie();
		$mollie->register_webhook();
	}

	/**
	 * Check payment status based on local order_id
	 *
	 * @since   1.0.0
	 * @param $order_id
	 * @return bool | string
	 */
	public function check_transaction($order_id) {

		if($order_id) {

			$transaction = new Kudos_Transaction();
			$transaction = $transaction->get_transaction_by(['order_id' => $order_id]);

			switch($transaction->status) {
				case 'paid':
					$vars = [
						'{{value}}' => (!empty($transaction->currency) ? html_entity_decode(get_currency_symbol($transaction->currency)) : '') . number_format_i18n($transaction->value, 2),
						'{{name}}' => $transaction->name
					];
					$return['header'] = strtr(get_option('_kudos_return_message_header'), $vars);
					$return['text'] = strtr(get_option('_kudos_return_message_text'), $vars);
					break;
				case 'canceled':
					$return['header'] = __('Payment canceled', 'kudos-donations');
	                break;
				default:
					$return['header'] = __('Thanks', 'kudos-donations');
					$return['text'] = __('Your donation will be processed soon.', 'kudos-donations');
					break;
			}

			return $return;
		}

		return false;
	}

	/**
	 * Gets url Mollie will use to return customer to after payment complete
	 *
	 * @since   1.0.0
	 * @return string|void
	 */
	public static function get_return_url() {
		$use_custom = get_option('_kudos_custom_return_enable');
		$custom_url = esc_url(get_option('_kudos_custom_return_url'));
		if($use_custom && $custom_url) {
			return $custom_url;
		} else {
			$returnUrl = is_ssl() ? 'https://' : 'http://';
			$returnUrl .= $_SERVER['HTTP_HOST'] . parse_url( $_SERVER["REQUEST_URI"], PHP_URL_PATH );
			return $returnUrl;
		}
	}

	/**
	 * Checks if required settings are saved before displaying button or modal
	 *
	 * @since   1.0.0
	 * @return bool
	 */
	public static function ready() {
		$apiConnected = get_option('_kudos_mollie_connected');
		$apiMode = get_option('_kudos_mollie_api_mode');
		$apiKey = get_option('_kudos_mollie_'.$apiMode.'_api_key');
		if($apiKey && $apiConnected) {
			return true;
		}
		return false;
	}

	/**
	 * Creates and registers the [kudos] shortcode and block
	 *
	 * @since   1.0.0
	 */
	public function register_kudos() {

		// Add shortcode
		add_shortcode( 'kudos', function ( $atts ) {

			$atts = shortcode_atts(
				[
					'label' => '',
					'donation_label' => '',
					'alignment' => '',
					'modalHeader' => '',
					'modalBody'  => ''
				],
				$atts,
				'kudos'
			);

			return $this->kudos_render_callback($atts);
		} );

		// Register kudos button block
		register_block_type( 'iseardmedia/kudos-button', [
			'editor_script' => $this->plugin_name . '-button-block',
		    'render_callback' => [$this, 'kudos_render_callback'],
		    'attributes' => [
		    	'buttonName' => [
		    	    'type' => 'string',
				    'default' => null
			    ],
		        'label' => [
					'type' => 'string',
		            'default' => get_option('_kudos_button_label'),
		        ],
				'alignment' => [
					'type' => 'string',
		            'default' => 'none',
		        ],
				'color' => [
					'type' => 'string',
		            'default' => get_option('_kudos_button_color')
		        ],
				'modalHeader' => [
					'type' => 'string',
		            'default' => get_option('_kudos_form_header')
		        ],
				'modalBody' => [
					'type' => 'string',
		            'default' => get_option('_kudos_form_text')
		        ],
				'id' => [
					'type' => 'string',
		            'source' => 'attribute',
		            'default' => 'kudos_modal-1',
		            'selector' => 'button.kudos_button',
		            'attribute' => 'data-target'
		        ]
			]
		]);
	}

	/**
	 * Renders the kudos button and donation modals
	 *
	 * @param $attr
	 *
	 * @return bool|string
	 * @since   1.1.0
	 */
	public function kudos_render_callback($attr) {

		// Create modal
		$modal = new Kudos_Modal();
		$modalId = $modal->get_id();
		$modal = $modal->get_payment_modal([
			'header' => $attr['modalHeader'],
			'donation_label' => (!empty($attr['buttonName']) ? $attr['buttonName'] : get_the_title()),
			'text' => $attr['modalBody'],
			'color' => (!empty($attr['color']) ? $attr['color'] : null)
		]);

		// Create button
		$button = new Kudos_Button([
			'button' => $attr['label'],
			'alignment' => $attr['alignment'],
			'color' => (!empty($attr['color']) ? $attr['color'] : null),
			'target' => $modalId
		]);

		// Return only if modal and button not empty
		if(!empty($modal) && !empty($button)) {
			return $button->get_button(false) . $modal;
		}

		return false;
	}

	/**
	 * Places message modal on page if conditions are met
     *
     * @since   1.0.0
	 */
	public function place_message_modal() {

		$modal = new Kudos_Modal();

		$token = sanitize_text_field(get_query_var('kudos_token'));
		$order_id = sanitize_text_field(get_query_var('kudos_order_id'));

		// Message modal
		if(!empty($order_id) && !empty($token)) {
			$order_id = base64_decode(sanitize_text_field($order_id));
			if(wp_verify_nonce($_REQUEST['kudos_token'],'kudos_check_order-' . $order_id)) {
				$atts = $this->check_transaction($order_id);
				echo $modal->get_message_modal($atts);
			}
		}
	}

	/**
	 * Register URL parameters
	 *
	 * @param $vars
	 *
	 * @return mixed
	 * @since   1.1.0
	 */
	public function register_vars($vars) {

		$vars[] = 'kudos_subscription_id';
		$vars[] = 'kudos_order_id';
		$vars[] = 'kudos_token';

		return $vars;
	}

	/**
	 * Checks for cancel subscription query vars and cancels subscription if valid
	 *
	 * @since   1.1.0
	 */
	public function get_cancel_vars() {

		$subscription_id = sanitize_text_field(get_query_var('kudos_subscription_id'));
		$token = sanitize_text_field(get_query_var('kudos_token'));
		$kudos_modal = new Kudos_Modal();

		if(!empty($token && !empty($subscription_id))) {

			$subscription_id = base64_decode($subscription_id);
			$kudos_subscription = new Kudos_Subscription();
			$subscription = $kudos_subscription->get_by(['subscription_id' => $subscription_id]);

			if($subscription && password_verify($subscription->customer_id, $token)) {
				$kudos_mollie = new Kudos_Mollie();
				if($kudos_mollie->cancel_subscription($subscription_id)) {
					echo $kudos_modal->get_message_modal([
						'header' => 'Subscription canceled',
						'text' => 'We will no longer be taking payments for this subscription. Thank you for your contributions.'
					]);
					return;
				}
			}
			echo $kudos_modal->get_message_modal([
				'header' => 'Link expired',
				'text' => 'Sorry, this link is no longer valid.'
			]);
		}
	}

	/**
	 * Processes the transaction. Used by action scheduler via mollie class.
	 *
	 * @param $transaction
	 *
	 * @return bool
	 * @since   1.1.0
	 */
	public static function process_transaction($transaction) {

		// Cast transaction array as object
		$transaction = (object) $transaction;

		// Send email receipt on success
		$mailer = new Kudos_Mailer();

		// Create invoice
		$kudos_invoice = new Kudos_Invoice();
		$kudos_invoice->generate_invoice($transaction);

		if($transaction->email) {

			// Send email - email setting is checked in mailer
			$mailer->send_receipt($transaction);

		}

		return true;
	}
}
