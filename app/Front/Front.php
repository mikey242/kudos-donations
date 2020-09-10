<?php

namespace Kudos\Front;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\MailerService;
use Kudos\Service\MapperService;
use Kudos\Service\MollieService;

/**
 * The public-facing functionality of the plugin.
 *
 * @link    https://www.linkedin.com/in/michael-iseard/
 * @since   1.0.0
 */

/**
 * The public-facing functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 */
class Front {

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
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since   1.0.0
	 */
	public function __construct( string $plugin_name, string $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . '-public', Utils::get_asset_url('kudos-public.css'), [], $this->version, 'all' );
		echo $this->get_kudos_root_styles();

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {

		$handle = $this->plugin_name . '-public';

		wp_enqueue_script( 'micromodal', plugin_dir_url( __FILE__ ) . '../../dist/js/vendor/micromodal.min.js', [], '0.4.6', true );
		wp_enqueue_script( 'jquery-validate', plugin_dir_url( __FILE__ ) . '../../dist/js/vendor/jquery.validate.min.js', [ 'jquery' ], '1.19.1', true );
		wp_enqueue_script( $handle, Utils::get_asset_url('kudos-public.js'), ['jquery', 'micromodal', 'jquery-validate'], $this->version, true );
		wp_localize_script( $handle, 'kudos', [
		        'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages');

	}

	/**
	 * Register the assets used for blocks.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_block_assets() {

		$handle = $this->plugin_name . '-button-block';

		wp_enqueue_style( $handle, Utils::get_asset_url('kudos-button-block.css'), [], $this->version, 'all' );
		wp_enqueue_script($handle, Utils::get_asset_url('kudos-button-block.js'), [ 'wp-i18n', 'wp-edit-post', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-api' ], $this->version, true );
		wp_localize_script($handle, 'kudos', [
			'theme_color' => Settings::get_setting('theme_color')
		]);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages');

	}

	/**
	 * Add root styles to header based on theme
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_kudos_root_styles() {

		$color = Settings::get_setting('theme_color');
		$color_dark = Utils::color_luminance($color, '-0.05');
		$color_darker = Utils::color_luminance($color, '-0.08');

		return "<style>

		:root {
			--kudos-theme-color: $color;
			--kudos-theme-color-dark: $color_dark;
			--kudos-theme-color-darker: $color_darker;
		}
		
		</style>";

	}

	/**
	 * Creates a payment with Mollie.
	 *
	 * @since   1.0.0
	 */
	public function submit_payment() {

		parse_str($_REQUEST['form'], $form);
		if(!wp_verify_nonce($form['_wpnonce'], 'kudos_submit')) {
			wp_send_json_error(['message' => __('Request invalid.', 'kudos-donations')]);
		}

		// Sanitize form fields
		$value = intval($form['value']);
		$payment_frequency = (!empty($form['recurring_frequency']) ? sanitize_text_field($form['recurring_frequency']) : 'oneoff');
		$recurring_length = (!empty($form['recurring_length']) ? intval($form['recurring_length']) : 0);
		$name = !empty($form['name']) ? sanitize_text_field($form['name']) : null;
		$email = !empty($form['email_address']) ? sanitize_email($form['email_address']) : null;
		$street = !empty($form['street']) ? sanitize_text_field($form['street']) : null;
		$postcode = !empty($form['postcode']) ? sanitize_text_field($form['postcode']) : null;
		$city = !empty($form['city']) ? sanitize_text_field($form['city']) : null;
		$country = !empty($form['country']) ? sanitize_text_field($form['country']) : null;
		$redirectUrl = !empty($form['return_url']) ? sanitize_text_field($form['return_url']) : null;
		$buttonName = !empty($form['donation_label']) ? sanitize_text_field($form['donation_label']) : null;

		$mollie = MollieService::factory();
		$mapper = new MapperService(DonorEntity::class);

		if($email) {

			// Search for existing donor
			/** @var DonorEntity $donor */
			$donor = $mapper->get_one_by([ 'email' => $email ]);

			// Create new donor
			if(empty($donor->customer_id)) {
				$donor = new DonorEntity();
				$customer = $mollie->create_customer($email, $name);
				$donor->set_fields([ 'customer_id' => $customer->id ]);
			}

			// Update new/existing donor
			$donor->set_fields([
				'email' => $email,
				'name' => $name,
				'street' => $street,
				'postcode' => $postcode,
				'city' => $city,
				'country' => $country
			]);

			$mapper->save($donor);
		}

		$customerId = $donor->customer_id ?? null;

		$payment = $mollie->create_payment($value, $payment_frequency, $recurring_length, $redirectUrl, $buttonName, $name, $email, $customerId);
		if($payment) {
			wp_send_json_success($payment->getCheckoutUrl());
		}

		wp_send_json_error(['message' => __('Error creating Mollie payment. Please try again later.', 'kudos-donations')]);

	}

	/**
	 * Check payment status based on local order_id
	 *
	 * @param string $order_id
	 * @return bool | array
	 * @since   1.0.0
	 */
	public function check_transaction( string $order_id) {

		if($order_id) {

			$mapper = new MapperService(TransactionEntity::class);
			/** @var TransactionEntity $transaction */
			$transaction = $mapper->get_one_by([ 'order_id' => $order_id]);

			if(NULL === $transaction) {
				return false;
			}

			$donor = $transaction->get_donor();

			switch($transaction->status) {
				case 'paid':
					$vars = [
						'{{value}}' => (!empty($transaction->currency) ? html_entity_decode(Utils::get_currency_symbol($transaction->currency)) : '') . number_format_i18n($transaction->value, 2),
						'{{name}}' => $donor->name,
						'{{email}}' => $donor->email
					];
					$return['title'] = strtr(Settings::get_setting('return_message_title'), $vars);
					$return['text'] = strtr(Settings::get_setting('return_message_text'), $vars);
					break;
				case 'canceled':
					$return['title'] = __('Payment canceled', 'kudos-donations');
	                break;
				default:
					$return['title'] = __('Thanks', 'kudos-donations');
					$return['text'] = __('Your donation will be processed soon.', 'kudos-donations');
					break;
			}

			return $return;
		}

		return false;
	}

	/**
	 * Checks if required settings are saved before displaying button or modal
	 *
	 * @since   1.0.0
	 * @return bool
	 */
	public static function ready() {

		$apiConnected = Settings::get_setting('mollie_connected');
		$apiMode = Settings::get_setting('mollie_api_mode');
		$apiKey = Settings::get_setting('mollie_'.$apiMode.'_api_key');

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
					'button_label'      => __('Donate now', 'kudos-donations'),
					'color'             => Settings::get_setting('theme_color'),
					'modal_title'      => __('Support us!', 'kudos-donations'),
					'welcome_text'      => __('Thank you for your donation. We appreciate your support!', 'kudos-donations'),
					'amount_type'       => 'open',
					'fixed_amounts'     => '5, 10, 20, 50',
					'donation_label'    => null,
					'alignment'         => 'none',
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
		    	'button_label' => [
		    	    'type' => 'string',
				    'default' => __('Donate now', 'kudos-donations')
			    ],
		        'donation_label' => [
					'type' => 'string',
		            'default' => null,
		        ],
				'alignment' => [
					'type' => 'string',
		            'default' => 'none',
		        ],
				'color' => [
					'type' => 'string',
		            'default' => Settings::get_setting('theme_color')
		        ],
				'modal_title' => [
					'type' => 'string',
		            'default' => __('Support us!', 'kudos-donations')
		        ],
				'welcome_text' => [
					'type' => 'string',
		            'default' => __('Thank you for your donation. We appreciate your support!', 'kudos-donations')
		        ],
			    'amount_type' => [
			    	'type' => 'string',
				    'default' => 'open'
			    ],
			    'fixed_amounts' => [
			    	'type' => 'string',
				    'default' => '5, 10, 20, 50'
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
	 * @param array $attr
	 * @return string|null
	 * @since   2.0.0
	 */
	public function kudos_render_callback( array $attr) {

		if(self::ready()) {

			// Create button and modal
			$button = new KudosButton($attr);
			$modal = $button->get_donate_modal();


			// Return only if modal and button not empty
			if(!empty($modal) && !empty($button)) {
				return $button->get_button(false) . $modal;
			}

			return null;

		} elseif(is_user_logged_in() && !is_admin()) {
			echo "<a href=". esc_url( admin_url('admin.php?page=kudos-settings')) .">" . __('Mollie not connected', 'kudos-donations') . "</a>";
		}

		return null;

	}

	/**
	 * Places message modal on page if conditions are met
     *
     * @since   1.0.0
	 */
	public function place_message_modal() {

		$token = sanitize_text_field(get_query_var('kudos_token'));
		$order_id = sanitize_text_field(get_query_var('kudos_order_id'));

		// Message modal
		if(!empty($order_id) && !empty($token)) {
			$order_id = base64_decode(sanitize_text_field($order_id));
			if(wp_verify_nonce($_REQUEST['kudos_token'],'kudos_check_order-' . $order_id)) {
				$atts = $this->check_transaction($order_id);
				if($atts) {
					$modal = new KudosModal();
					echo $modal->get_message_modal($atts);
				}
			}
		}
	}

	/**
	 * Register URL parameters
	 *
	 * @param $vars
	 * @return mixed
	 * @since   2.0.0
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
	 * @since   2.0.0
	 */
	public function get_cancel_vars() {

		$subscription_id = sanitize_text_field(get_query_var('kudos_subscription_id'));
		$token = get_query_var('kudos_token');  // Don't sanitize!

		if(!empty($token && !empty($subscription_id))) {

			$kudos_modal = new KudosModal();
			$subscription_id = base64_decode($subscription_id);
			$mapper = new MapperService(SubscriptionEntity::class);

			/** @var SubscriptionEntity $subscription */
			$subscription = $mapper->get_one_by(['subscription_id' => $subscription_id]);

			// Bail if no subscription found
			if(NULL === $subscription) {
				return;
			}

			$donor = $subscription->get_donor();

			if($donor->verify_secret($token)) {
				$kudos_mollie = MollieService::factory();
				if($kudos_mollie->cancel_subscription($subscription_id)) {
					echo $kudos_modal->get_message_modal([
						'header' => __('Subscription canceled', 'kudos-donations'),
						'text' => __('We will no longer be taking payments for this subscription. Thank you for your contributions.', 'kudos-donations')
					]);
					return;
				}
			}

			echo $kudos_modal->get_message_modal([
				'header' => __('Link expired', 'kudos-donations'),
				'text' => __('Sorry, this link is no longer valid.', 'kudos-donations')
			]);
		}

	}

	/**
	 * Processes the transaction. Used by action scheduler via mollie class.
	 *
	 * @param string $order_id
	 * @return bool
	 * @since   2.0.0
	 */
	public static function process_transaction( string $order_id) {

		$logger = new LoggerService();

		if(NULL === $order_id) {
			$logger->error('Order id not provided to process_transaction function.');
		}

		do_action('kudos_process_transaction', $order_id);

		$logger->debug('Processing transaction', [$order_id]);

		$mapper = new MapperService(TransactionEntity::class);
		/** @var TransactionEntity $transaction */
		$transaction = $mapper->get_one_by([ 'order_id' => $order_id ]);

		if($transaction->get_donor()->email) {
			// Send email - email setting is checked in mailer
			$mailer = MailerService::factory();
			$mailer->send_receipt($transaction);
		}

		return true;

	}

	/**
	 * Remove secret key associated with donor
	 *
	 * @param string $customer_id
	 * @return bool|int
	 * @since   2.0.0
	 */
	public static function remove_donor_secret( string $customer_id) {

		if($customer_id) {
			$mapper = new MapperService(DonorEntity::class);
			/** @var DonorEntity $donor */
			$donor = $mapper->get_one_by(['customer_id' => $customer_id]);
			$donor->clear_secret();
			return $mapper->save($donor);
		}
		return false;

	}
}
