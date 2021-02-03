<?php

namespace Kudos\Front;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Campaigns;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;

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
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since   1.0.0
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
	 * @since   1.0.0
	 */
	public function __construct( string $plugin_name, string $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			$this->plugin_name . '-public',
			Utils::get_asset_url( 'kudos-public.css' ),
			[],
			$this->version
		);
		echo $this->get_kudos_root_styles();

	}

	/**
	 * Add root styles to header based on theme
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function get_kudos_root_styles(): string {

		$theme_colours    = Settings::get_setting( 'theme_colors' );

		$primary          = isset($theme_colours['primary']) ? $theme_colours['primary'] : '#ff9f1c';
		$primary_dark     = Utils::color_luminance( $primary, '-0.06' );
		$primary_darker   = Utils::color_luminance( $primary, '-0.09' );
		$secondary        = isset($theme_colours['secondary']) ? $theme_colours['secondary'] : '#2ec4b6';
		$secondary_dark   = Utils::color_luminance( $secondary, '-0.06' );
		$secondary_darker = Utils::color_luminance( $secondary, '-0.09' );

		return "<style>

		:root {
			--kudos-theme-primary: $primary;
			--kudos-theme-primary-dark: $primary_dark;
			--kudos-theme-primary-darker: $primary_darker;
			--kudos-theme-secondary: $secondary;
			--kudos-theme-secondary-dark: $secondary_dark;
			--kudos-theme-secondary-darker: $secondary_darker;
		}
		
		</style>";

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since   1.0.0
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
			'1.19.1',
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
				'createPaymentUrl' => rest_url( PaymentService::REST_NAMESPACE . PaymentService::PAYMENT_ROUTE ),
			]
		);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );

	}

	/**
	 * Register the assets used for blocks.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_block_assets() {

		$handle = $this->plugin_name . '-button-block';

		wp_enqueue_style( $handle, Utils::get_asset_url( 'kudos-button-block.css' ), [], $this->version );
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
		echo $this->get_kudos_root_styles();

	}

	/**
	 * Checks if required api settings are saved before displaying button
	 *
	 * @return bool
	 * @since   1.0.0
	 */
	public static function api_ready(): bool {

		$vendor        = Settings::get_setting( 'payment_vendor' );
		$api_connected = Settings::get_setting( $vendor . '_connected' );
		$api_mode      = Settings::get_setting( $vendor . '_api_mode' );
		$api_key       = Settings::get_setting( $vendor . '_' . $api_mode . '_api_key' );

		if ( ! $api_connected && ! $api_key ) {
			return false;
		}

		return true;

	}

	/**
	 * Register query parameters
	 *
	 * @param array $vars Current query vars.
	 *
	 * @return array
	 * @since   2.0.0
	 */
	public function register_vars( array $vars ): array {

		$vars[] = 'kudos_subscription_id';
		$vars[] = 'kudos_order_id';
		$vars[] = 'kudos_token';

		return $vars;

	}

	/**
	 * Creates and registers the [kudos] shortcode and block
	 *
	 * @since   1.0.0
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
					'id'           => [
						'type'      => 'string',
						'source'    => 'attribute',
						'default'   => 'kudos_modal-1',
						'selector'  => 'button.kudos_button',
						'attribute' => 'data-target',
					],
				],
			]
		);
	}

	/**
	 * Renders the kudos button and donation modals
	 *
	 * @param array $atts Array of kudos button/modal attributes.
	 *
	 * @return string|null
	 * @since   2.0.0
	 */
	public function kudos_render_callback( array $atts ): ?string {

		// Continue only if payment API ready
		if ( self::api_ready() ) {

			// Set campaign according to atts
			$campaigns = new Campaigns();
			if ( ! empty( $atts['campaign_id'] ) ) {
				$campaign          = $campaigns->get_campaign( $atts['campaign_id'] );
				$campaign['total'] = $campaigns::get_campaign_total( $atts['campaign_id'] );
			}

			// Bail if no campaign found
			if ( empty( $campaign ) ) {
				return null;
			}

			// Add campaign config to atts
			$atts['campaign'] = $campaign;

			// Generate markup.
			$button = new KudosButton( $atts );
			return $button->get_markup();

		}

		return null;

	}

	/**
	 * Handles the various query variables and shows relevant modals
	 *
	 * @since 2.0.0
	 */
	public function handle_query_variables() {

		if ( isset( $_REQUEST['kudos_action'] ) && - 1 !== $_REQUEST['kudos_action'] ) {

			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$token  = sanitize_text_field( get_query_var( 'kudos_token' ) );

			switch ( $action ) {

				case 'order_complete':
					$order_id = sanitize_text_field( get_query_var( 'kudos_order_id' ) );
					// Return message modal.
					if ( ! empty( $order_id ) && ! empty( $token ) ) {
						$order_id    = sanitize_text_field( $order_id );
						$mapper      = new MapperService( TransactionEntity::class );
						$transaction = $mapper->get_one_by( [ 'order_id' => $order_id ] );
						if ( $transaction && $transaction->verify_secret( $token ) ) {
							$atts = $this->check_transaction( $order_id );
							if ( $atts ) {
								$modal = new KudosModal();
								echo $modal->get_message_modal( $atts );
							}
						}
					}
					break;

				case 'cancel_subscription':
					$subscription_id = sanitize_text_field( get_query_var( 'kudos_subscription_id' ) );
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
								echo $modal->get_message_modal(
									[
										'modal_title' => __( 'Subscription cancelled', 'kudos-donations' ),
										'modal_text'  => __( 'We will no longer be taking payments for this subscription. Thank you for your contributions.',
											'kudos-donations' ),
									]
								);

								return;
							}
						}

						echo $modal->get_message_modal(
							[
								'modal_title' => __( 'Link expired', 'kudos-donations' ),
								'modal_text'  => __( 'Sorry, this link is no longer valid.', 'kudos-donations' ),
							]
						);
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
	 * @since   1.0.0
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
			$donor         = $transaction->get_donor();
			$campaigns     = new Campaigns();
			$campaign      = $campaigns->get_campaign( $transaction->campaign_id );
			$campaign_name = ! empty( $campaign['name'] ) ? $campaign['name'] : '';

			switch ( $transaction->status ) {
				case 'paid':
					$vars                = [
						'{{value}}'    => ( ! empty( $transaction->currency ) ? html_entity_decode( Utils::get_currency_symbol( $transaction->currency ) ) : '' ) . number_format_i18n( $transaction->value, 2 ),
						'{{name}}'     => $donor->name,
						'{{email}}'    => $donor->email,
						'{{campaign}}' => $campaign_name
					];
					$atts['modal_title'] = strtr( Settings::get_setting( 'return_message_title' ), $vars );
					$atts['modal_text']  = strtr( Settings::get_setting( 'return_message_text' ), $vars );
					break;
				case 'canceled':
					$atts['modal_title'] = __( 'Payment cancelled', 'kudos-donations' );
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
