<?php

namespace Kudos\Controller;

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
use Kudos\Service\TwigService;
use Kudos\View\Model\Button;
use Kudos\View\Model\Form;
use Kudos\View\Model\Message;
use Kudos\View\Model\Modal;

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
	 * @var LoggerService
	 */
	private $logger;
	/**
	 * @var PaymentService
	 */
	private $payment;
	/**
	 * @var TwigService
	 */
	private $twig;
	/**
	 * @var MapperService
	 */
	private $mapper;
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 */
	public function __construct(
		string $plugin_name,
		string $version,
		LoggerService $logger,
		PaymentService $payment,
		TwigService $twig,
		MapperService $mapper,
		Settings $settings
	) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->logger      = $logger;
		$this->payment     = $payment;
		$this->twig        = $twig;
		$this->mapper      = $mapper;
		$this->settings    = $settings;

	}

	/**
	 * Add root styles to header based on theme.
	 *
	 * @param bool $echo Whether to echo the styles instead of returning a string.
	 *
	 * @return string
	 */
	public function get_kudos_root_styles( bool $echo = true ): string {

		$theme_colours = apply_filters( 'kudos_theme_colors', Settings::get_setting( 'theme_colors' ) );

		$primary          = $theme_colours['primary'] ?? '#ff9f1c';
		$primary_dark     = Utils::color_luminance( $primary, '-0.06' );
		$primary_darker   = Utils::color_luminance( $primary, '-0.09' );
		$secondary        = $theme_colours['secondary'] ?? '#2ec4b6';
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
	 * Register the JavaScript for the public-facing side of the plugin.
	 */
	public function enqueue_scripts() {

		$handle = $this->plugin_name . '-public';

		wp_enqueue_script(
			$handle,
			Utils::get_asset_url( '/js/kudos-public.js' ),
			[ 'jquery', 'lodash' ],
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

		$handle = $this->plugin_name . '-block';

		// Enqueue block specific js.
		wp_enqueue_style(
			$handle,
			Utils::get_asset_url( '/css/kudos-public.css' ),
			[],
			$this->version
		);

		wp_enqueue_script(
			$handle,
			Utils::get_asset_url( '/js/kudos-button-block.js' ),
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
		echo $this->get_kudos_root_styles();

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
						'type'         => 'button',
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
					'type'         => [
						'type'    => 'string',
						'default' => 'button',
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
			if ( ! $this->payment::is_api_ready() ) {
				/* translators: %s: Payment vendor (e.g. Mollie). */
				throw new Exception( sprintf( __( "%s not connected.", 'kudos-donations' ),
					$this->payment::get_vendor_name() ) );
			}

			// Twig service.
			$twig = $this->twig;

			// Create the form based on campaign id.
			$form = $this->create_form( $atts['campaign_id'] );
			$form = $this->twig->render( Form::TEMPLATE, $form->to_array() );

			// If type set and true then return form.
			if ( isset( $atts['type'] ) && $atts['type'] === 'form' ) {
				return $form;
			}

			// Otherwise create modal and button and return their html.
			$modal = new Modal();
			$modal->set_content( $form );
			$button = new Button();
			$button->set_atts( $atts );
			$button->set_target( $modal->get_container_id() );

			return $twig->render( Button::TEMPLATE, $button->to_array() ) .
			       $this->render_modal( $modal );


		} catch ( Exception $e ) {

			// Display error message if thrown and user is admin.
			if ( current_user_can( 'manage_options' ) ) {
				return '<p>' . $e->getMessage() . '</p>';
			}
		}

		// Nothing displayed to visitors if there is a problem.
		return null;

	}

	/**
	 * Wrapper for rendering a modal. Places markup in footer if setting enabled.
	 *
	 * @param $modal
	 *
	 * @return string|null
	 */
	private function render_modal( $modal ): ?string {

		// Get markup for modal.
		$modal = $this->twig->render( Modal::TEMPLATE, $modal->to_array() );

		// Place markup in footer if setting enabled.
		if ( Settings::get_setting( 'modal_in_footer' ) ) {
			add_action( 'wp_footer',
				function () use ( $modal ) {
					echo $modal;
				} );

			// No need to return anything.
			return null;
		} else {

			// Otherwise return the modal.
			return $modal;
		}
	}

	/**
	 * Builds the form object from supplied campaign_id.
	 *
	 * @throws Exception
	 */
	private function create_form( $campaign_id ): Form {
		$form = new Form();
		$form->set_campaign( $campaign_id );
		$campaign_stats = $this->settings->get_campaign_stats( $campaign_id );
		$form->set_campaign_stats( $campaign_stats );

		return $form;
	}

	/**
	 * Create message modal with supplied header and body text.
	 *
	 * @param string $header The header text.
	 * @param string $body The body text.
	 *
	 * @return string
	 */
	private function create_message_modal( string $header, string $body ): ?string {

		$message = new Message();
		$message->set_title( $header );
		$message->set_body( $body );
		$modal = new Modal();
		$modal->set_content( $this->twig->render( Message::TEMPLATE, $message->to_array() ) );
		$modal->set_class( 'kudos-message-modal' );

		return $this->twig->render(Modal::TEMPLATE, $modal->to_array());
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
						$transaction = $this->mapper
							->get_repository( TransactionEntity::class )
							->get_one_by( [ 'order_id' => $order_id ] );

						if ( $transaction && $transaction->verify_secret( $token ) ) {
							$atts = $this->check_transaction( $order_id );
							if ( $atts ) {
								echo $this->create_message_modal( $atts['modal_title'], $atts['modal_text'] );
							}
						}
					}
					break;

				case 'cancel_subscription':
					$subscription_id = sanitize_text_field( $_REQUEST['kudos_subscription_id'] );
					// Cancel subscription modal.
					if ( ! empty( $token && ! empty( $subscription_id ) ) ) {

						/** @var SubscriptionEntity $subscription */
						$subscription = $this->mapper
							->get_repository( SubscriptionEntity::class )
							->get_one_by( [ 'subscription_id' => $subscription_id ] );

						// Bail if no subscription found.
						if ( null === $subscription ) {
							return;
						}

						if ( $subscription->verify_secret( $token ) ) {
							if ( $this->payment->cancel_subscription( $subscription_id ) ) {
								echo $this->create_message_modal(
									__( 'Subscription cancelled', 'kudos-donations' ),
									__( 'We will no longer be taking payments for this subscription. Thank you for your contributions.',
										'kudos-donations' )
								);

								return;
							}
						}

						echo $this->create_message_modal(
							__( 'Link expired', 'kudos-donations' ),
							__( 'Sorry, this link is no longer valid.', 'kudos-donations' )
						);
					}
					break;
			}
		}
	}

	/**
	 * Returns the kudos logo SVG markup.
	 *
	 * @param string|null $color
	 * @param int $width
	 *
	 * @return string|null
	 */
	public function get_kudos_logo_markup( string $color = null, int $width = 24 ): ?string {

		if ( $color ) {
			$lineColor  = $color;
			$heartColor = $color;
		} else {
			$lineColor  = '#2ec4b6';
			$heartColor = '#ff9f1c';
		}

		return apply_filters( 'kudos_get_kudos_logo',
			$this->twig->render( 'public/logo.html.twig',
				[
					'width'      => $width,
					'lineColor'  => $lineColor,
					'heartColor' => $heartColor,
				] ),
			$width );
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

			$mapper = $this->mapper;

			/** @var TransactionEntity $transaction */
			$transaction = $mapper
				->get_repository( TransactionEntity::class )
				->get_one_by( [ 'order_id' => $order_id ] );

			if ( null === $transaction ) {
				return false;
			}

			/** @var DonorEntity $donor */
			$donor = $mapper
				->get_repository( DonorEntity::class )
				->get_one_by( [ 'customer_id' => $transaction->customer_id ] );

			try {
				$campaign = Settings::get_campaign( $transaction->campaign_id );
			} catch ( Exception $e ) {
				$logger = $this->logger;
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
