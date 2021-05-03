<?php

namespace Kudos\Front;

use Exception;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;
use Kudos\Service\RestRouteService;

/**
 * The public-facing functionality of the plugin.
 *
 * @link    https://www.linkedin.com/in/michael-iseard/
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
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 */
	public function __construct( string $plugin_name, string $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Add root styles to header based on theme.
	 *
	 * @param bool $echo Whether to echo the styles instead of returning a string.
	 *
	 */
	public function get_kudos_root_styles( $echo = true ): string {

		$theme_colours = apply_filters( 'kudos_theme_colors', Settings::get_setting( 'theme_colors' ) );

		$primary          = $theme_colours['primary'] ?? '#ff9f1c';
		$primary_dark     = Utils::color_luminance( $primary, '-0.06' );
		$primary_darker   = Utils::color_luminance( $primary, '-0.09' );
		$secondary        = $theme_colours['secondary'] ?? '#2ec4b6';
		$secondary_dark   = Utils::color_luminance( $secondary, '-0.06' );
		$secondary_darker = Utils::color_luminance( $secondary, '-0.09' );

		$out = "<style>

		:root {
			--kudos-theme-primary: $primary;
			--kudos-theme-primary-dark: $primary_dark;
			--kudos-theme-primary-darker: $primary_darker;
			--kudos-theme-secondary: $secondary;
			--kudos-theme-secondary-dark: $secondary_dark;
			--kudos-theme-secondary-darker: $secondary_darker;
		}
		
		</style>";

		if ( $echo ) {
			echo $out;
		}

		return $out;

	}

	/**
	 * Register the JavaScript for the public-facing side of the plugin.
	 */
	public function enqueue_scripts() {

		$handle = $this->plugin_name . '-public';

		wp_enqueue_script(
			'micromodal',
			plugin_dir_url( __FILE__ ) . '../../dist/js/vendor/micromodal.min.js',
			[],
			'0.4.6',
			true
		);
		wp_enqueue_script(
			'jquery-validate',
			plugin_dir_url( __FILE__ ) . '../../dist/js/vendor/jquery.validate.min.js',
			[ 'jquery' ],
			'1.19.3',
			true
		);
		wp_enqueue_script(
			$handle,
			Utils::get_asset_url( 'kudos-public.js' ),
			[ 'jquery', 'micromodal', 'jquery-validate' ],
			$this->version,
			true
		);
		wp_localize_script(
			$handle,
			'kudos',
			[
				'_wpnonce'         => wp_create_nonce( 'wp_rest' ),
				'createPaymentUrl' => rest_url( RestRouteService::NAMESPACE . RestRouteService::PAYMENT_CREATE ),
			]
		);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );

	}

	/**
	 * Register the assets used for blocks.
	 */
	public function enqueue_block_assets() {

		// Enqueue public css.
		wp_enqueue_style( $this->plugin_name . '-public',
			Utils::get_asset_url( 'kudos-public.css' ),
			[],
			$this->version );

		// Enqueue block specific js.
		$handle = $this->plugin_name . '-button-block';
		wp_enqueue_script(
			$handle,
			Utils::get_asset_url( 'kudos-button-block.js' ),
			[
				'wp-i18n',
				'wp-edit-post',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-data',
				'wp-plugins',
				'wp-edit-post',
				'wp-api',
			],
			$this->version,
			true
		);
		wp_localize_script(
			$handle,
			'kudos',
			[
				'color_primary' => Settings::get_setting( 'color_primary' ),
			]
		);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );

		// Output root styles.
		$this->get_kudos_root_styles();

	}

	/**
	 * Creates and registers the [kudos] shortcode and block.
	 */
	public function register_kudos() {

		// Add shortcode.
		add_shortcode(
			'kudos',
			function ( $atts ) {

				$atts = shortcode_atts(
					[
						'button_label' => __( 'Donate now', 'kudos-donations' ),
						'campaign_id'  => 'default',
						'alignment'    => 'none',
					],
					$atts,
					'kudos'
				);

				return $this->kudos_render_callback( $atts );
			}
		);

		// Register kudos button block.
		register_block_type(
			'iseardmedia/kudos-button',
			[
				'editor_script'   => $this->plugin_name . '-button-block',
				'render_callback' => [ $this, 'kudos_render_callback' ],
				'attributes'      => [
					'button_label' => [
						'type'    => 'string',
						'default' => __( 'Donate now', 'kudos-donations' ),
					],
					'campaign_id'  => [
						'type'    => 'string',
						'default' => 'default',
					],
					'alignment'    => [
						'type'    => 'string',
						'default' => 'none',
					],
				],
			]
		);
	}

	/**
	 * Renders the kudos button and donation modals.
	 *
	 * @param array $atts Array of Kudos button/modal attributes.
	 *
	 * @return string|null
	 */
	public function kudos_render_callback( array $atts ): ?string {

		try {

			// Check if the current vendor is connected, otherwise throw an exception.
			if ( ! PaymentService::is_api_ready() ) {
				/* translators: %s: Payment vendor (e.g. Mollie). */
				throw new Exception( sprintf( __( "%s not connected.", 'kudos-donations' ),
					PaymentService::get_vendor_name() ) );
			}

			// Generate markup.
			$button = new KudosButton($atts);
			return $button->render();


		} catch ( Exception $e ) {

			// Display error message if thrown thrown.
			if ( current_user_can( 'manage_options' ) ) {
				return '<p>' . $e->getMessage() . '</p>';
			}
		}

		// Nothing displayed to visitors if there is a problem.
		return null;

	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables() {

		if ( isset( $_REQUEST['kudos_action'] ) && - 1 !== $_REQUEST['kudos_action'] ) {

			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$token  = sanitize_text_field( wp_unslash( $_REQUEST['kudos_token'] ) );

			switch ( $action ) {

				case 'order_complete':
					$order_id = sanitize_text_field( $_REQUEST['kudos_order_id'] );
					// Return message modal.
					if ( ! empty( $order_id ) && ! empty( $token ) ) {
						$mapper      = new MapperService( TransactionEntity::class );
						$transaction = $mapper->get_one_by( [ 'order_id' => $order_id ] );
						if ( $transaction && $transaction->verify_secret( $token ) ) {
							$atts = $this->check_transaction( $order_id );
							if ( $atts ) {
								$modal = new KudosModal();
								$modal->create_message_modal( $atts['modal_title'],$atts['modal_text'] );
								echo $modal->render();
							}
						}
					}
					break;

				case 'cancel_subscription':
					$subscription_id = sanitize_text_field( $_REQUEST['kudos_subscription_id'] );
					// Cancel subscription modal.
					if ( ! empty( $token && ! empty( $subscription_id ) ) ) {

						$mapper = new MapperService( SubscriptionEntity::class );

						/** @var SubscriptionEntity $subscription */
						$subscription = $mapper->get_one_by( [ 'subscription_id' => $subscription_id ] );

						// Bail if no subscription found.
						if ( null === $subscription ) {
							return;
						}

						$modal = new KudosModal();

						if ( $subscription->verify_secret( $token ) ) {
							$payment_service = PaymentService::factory();
							if ( $payment_service->cancel_subscription( $subscription_id ) ) {
								$modal->create_message_modal(
									__( 'Subscription cancelled', 'kudos-donations' ),
									__( 'We will no longer be taking payments for this subscription. Thank you for your contributions.',
										'kudos-donations' )
								);
								echo $modal->render();

								return;
							}
						}

						$modal->create_message_modal(
							__( 'Link expired', 'kudos-donations' ),
							__( 'Sorry, this link is no longer valid.', 'kudos-donations' )
						);
						$modal->render();
					}
					break;
			}
		}
	}

	/**
	 * Check payment status based on local order_id
	 *
	 * @param string $order_id Kudos order id.
	 *
	 * @return bool | array
	 */
	public function check_transaction( string $order_id ) {

		if ( $order_id ) {

			$mapper = new MapperService( TransactionEntity::class );
			/** @var TransactionEntity $transaction */
			$transaction = $mapper->get_one_by( [ 'order_id' => $order_id ] );

			if ( null === $transaction ) {
				return false;
			}

			/** @var DonorEntity $donor */
			$donor = $transaction->get_donor();

			try {
				$campaign = Settings::get_campaign( $transaction->campaign_id );
			} catch ( Exception $e ) {
				$logger = LoggerService::factory();
				$logger->warning( 'Error checking transaction: ' . $e->getMessage() );
			}

			$campaign_name = $campaign['name'] ?? '';

			switch ( $transaction->status ) {
				case 'paid':
					$vars                = [
						'{{value}}'    => ( ! empty( $transaction->currency ) ? html_entity_decode( Utils::get_currency_symbol( $transaction->currency ) ) : '' ) . number_format_i18n( $transaction->value,
								2 ),
						'{{name}}'     => $donor->name,
						'{{email}}'    => $donor->email,
						'{{campaign}}' => $campaign_name,
					];
					$atts['modal_title'] = strtr( Settings::get_setting( 'return_message_title' ), $vars );
					$atts['modal_text']  = strtr( Settings::get_setting( 'return_message_text' ), $vars );
					break;
				case 'canceled':
					$atts['modal_title'] = __( 'Payment cancelled', 'kudos-donations' );
					$atts['modal_text']  = __( 'You have not been charged for this transaction.', 'kudos-donations' );
					break;
				default:
					$atts['modal_title'] = __( 'Thanks', 'kudos-donations' );
					$atts['modal_text']  = __( 'Your donation will be processed soon.', 'kudos-donations' );
					break;
			}

			return $atts;
		}

		return false;
	}
}
