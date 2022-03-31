<?php

namespace Kudos\Controller;

use Exception;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Assets;
use Kudos\Helpers\Campaign;
use Kudos\Helpers\CustomPostType;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;
use Kudos\Service\TwigService;

class Front {

	/*
	 * The twig template file used to render the donate button.
	 */
	const BUTTON_TEMPLATE = 'public/button/donate.button.html.twig';

	/*
	 * The twig template file used to render the donation tabs.
	 */
	const FORM_TEMPLATE = 'public/forms/donate.tabs.html.twig';

	/*
	 * The twig template used to render the modal template.
	 */
	const MODAL_TEMPLATE = 'public/modal/base.html.twig';

	/*
	 * The twig template used to render a message for use in a modal.
	 */
	const MESSAGE_TEMPLATE = 'public/modal/_message.html.twig';

	/*
	 * The twig template used as a wrapper for the above templates.
	 * Required for css targeting.
	 */
	const WRAPPER_TEMPLATE = 'public/wrapper.html.twig';

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
	 * Initialize the class and set its properties.
	 *
	 * @param string $version The version of this plugin.
	 *
	 */
	public function __construct(
		string $version,
		LoggerService $logger,
		PaymentService $payment,
		TwigService $twig,
		MapperService $mapper
	) {
		$this->version = $version;
		$this->logger  = $logger;
		$this->payment = $payment;
		$this->twig    = $twig;
		$this->mapper  = $mapper;
	}

	public static function test_callback() {
		wp_send_json_error( 'no' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the plugin.
	 * This is necessary in order to localize the script with variables.
	 */
	public function register_scripts() {

		$public_js = Assets::get_script( '/public/kudos-public.js' );
		wp_register_script(
			'kudos-donations-public',
			$public_js['url'],
			$public_js['dependencies'],
			$public_js['version'],
			true
		);

		wp_localize_script(
			'kudos-donations-public',
			'kudos',
			[
				'_wpnonce' => wp_create_nonce( 'wp_rest' ),
			]
		);

		wp_set_script_translations( 'kudos-donations-public', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );

	}

	/**
	 * Register the public facing styles.
	 */
	public function register_styles() {

		wp_register_style(
			'kudos-donations-public',
			Assets::get_asset_url( '/public/kudos-public.css' ),
			[ 'kudos-donations-root' ],
			$this->version
		);

	}

	/**
	 * Register the inline root styles used by block editor and front.
	 */
	public function register_root_styles() {
		wp_register_style( 'kudos-donations-root', false );
	}

	/**
	 * Registers the button shortcode and block.
	 */
	public function register_kudos() {

		$this->register_button_block();
		$this->register_post_types();

		// If setting is not enabled the shortcode assets and registration will be skipped.
		if ( Settings::get_setting( 'enable_shortcode' ) ) {
			$this->register_button_shortcode();
		}
	}

	/**
	 * Register the Kudos button block.
	 */
	private function register_button_block() {

		register_block_type( 'iseardmedia/kudos-button',
			[
				"render_callback" => [ $this, "kudos_render_callback" ],
				"category"        => "widgets",
				"title"           => "Kudos Button",
				"description"     => "Adds a Kudos donate button or tabs to your post or page.",
				"keywords"        => [
					"kudos",
					"button",
					"donate",
				],
				"supports"        => [
					"align"           => false,
					"customClassName" => true,
					"typography"      => [
						"fontSize" => false,
					],
				],
				"example"         => [
					"attributes" => [
						"label"     => "Donate now!",
						"alignment" => "center",
					],
				],
				"attributes"      => [
					"button_label" => [
						"type"    => "string",
						"default" => "Donate now",
					],
					"campaign_id"  => [
						"type"    => "string",
						"default" => "default",
					],
					"alignment"    => [
						"type"    => "string",
						"default" => "none",
					],
					"type"         => [
						"type"    => "string",
						"default" => "button",
					],
				],
				"editor_script"   => "kudos-donations-editor",
				"editor_style"    => "kudos-donations-public",
				"script"          => "kudos-donations-public",
				"style"           => "kudos-donations-public",
			] );
	}

	private function register_post_types() {
		new CustomPostType( 'kudos_campaign', [], [
			'goal'             => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'additional_funds' => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_title'    => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_text'     => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'address_enabled'  => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'address_required' => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'message_enabled'  => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'amount_type'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'fixed_amounts'    => [
				'type'              => 'string',
				'single'            => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'donation_type'    => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'show_progress'    => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'theme_color'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		] );
	}

	/**
	 * Register the kudos button shortcode.
	 */
	private function register_button_shortcode() {

		// Enqueue necessary resources.
		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_script( 'kudos-donations-public' );
			wp_enqueue_style( 'kudos-donations-public' );
		} );

		// Register shortcode.
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

			$alignment = 'has-text-align-' . $atts['alignment'] ?? 'none';

			return "<kudos-donations class='block $alignment' label='" . $atts['button_label'] . "' align='" . $atts['alignment'] . "' campaign='" . $atts['campaign_id'] . "'>
					</kudos-donations>";

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
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables() {

		if ( isset( $_REQUEST['kudos_action'] ) && - 1 !== $_REQUEST['kudos_action'] ) {

			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$nonce  = sanitize_text_field( wp_unslash( $_REQUEST['kudos_nonce'] ) );

			switch ( $action ) {

				case 'order_complete':
					$order_id = sanitize_text_field( $_REQUEST['kudos_order_id'] );
					// Return message modal.
					if ( ! empty( $order_id ) && ! empty( $nonce ) ) {
						$transaction = $this->mapper
							->get_repository( TransactionEntity::class )
							->get_one_by( [ 'order_id' => $order_id ] );
						if ( $transaction && wp_verify_nonce( $nonce, $action . $order_id ) ) {
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
					if ( ! empty( $nonce && ! empty( $subscription_id ) ) ) {

						/** @var SubscriptionEntity $subscription */
						$subscription = $this->mapper
							->get_repository( SubscriptionEntity::class )
							->get_one_by( [ 'subscription_id' => $subscription_id ] );

						// Bail if no subscription found.
						if ( null === $subscription ) {
							return;
						}

						if ( wp_verify_nonce( $nonce, $action ) ) {
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
				$campaign = Campaign::get_campaign( $transaction->campaign_id );
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

	/**
	 * Create message modal with supplied header and body text.
	 *
	 * @param string $header The header text.
	 * @param string $body The body text.
	 *
	 * @return string
	 */
	private function create_message_modal( string $header, string $body ): ?string {

		$twig = $this->twig;

		$message = $twig->render( self::MESSAGE_TEMPLATE,
			[
				'header_text' => $header,
				'body_text'   => $body,
			] );

		$modal = $twig->render( self::MODAL_TEMPLATE,
			[
				'id'      => Utils::generate_id(),
				'content' => $message,
				'class'   => 'kudos-message-modal',
			] );

		return $this->render_wrapper( $modal );
	}

	/**
	 * Returns the html in a wrapper element.
	 *
	 * @param string $content
	 * @param string $alignment
	 *
	 * @return bool|string
	 */
	protected function render_wrapper( string $content, string $alignment = 'none' ) {
		return $this->twig->render( self::WRAPPER_TEMPLATE,
			[
				'content'   => $content,
				'alignment' => $alignment,
			]
		);
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
	 * Builds the tabs object from supplied campaign_id.
	 *
	 * @param string $campaign_id
	 * @param string $id
	 *
	 * @return string
	 * @throws Exception
	 */
	private function create_form( string $campaign_id, string $id ): string {

		$campaign       = Campaign::get_campaign( $campaign_id );
		$transactions   = $this->mapper->get_repository( TransactionEntity::class )
		                               ->get_all_by( [
			                               'campaign_id' => $campaign_id,
		                               ] );
		$campaign_stats = Campaign::get_campaign_stats( $transactions );

		$atts = [
			'id'                => $id,
			'return_url'        => Utils::get_return_url(),
			'privacy_link'      => Settings::get_setting( 'privacy_link' ),
			'terms_link'        => Settings::get_setting( 'terms_link' ),
			'recurring_allowed' => isset( Settings::get_current_vendor_settings()['recurring'] ) ?? false,
			'spam_protection'   => Settings::get_setting( 'spam_protection' ),
			'vendor_name'       => Settings::get_setting( 'payment_vendor' ),
			'campaign_id'       => $campaign['id'],
			'button_label'      => $campaign['button_label'] ?? '',
			'welcome_title'     => $campaign['modal_title'] ?? '',
			'welcome_text'      => $campaign['welcome_text'] ?? '',
			'campaign_goal'     => $campaign['campaign_goal'] ?? '',
			'show_progress'     => $campaign['show_progress'] ?? '',
			'amount_type'       => $campaign['amount_type'] ?? '',
			'fixed_amounts'     => $campaign['fixed_amounts'] ?? '',
			'frequency'         => $campaign['donation_type'] ?? '',
			'address_enabled'   => $campaign['address_enabled'] ?? '',
			'address_required'  => $campaign['address_required'] ?? '',
			'message_enabled'   => $campaign['message_enabled'] ?? '',
			'campaign_stats'    => $campaign_stats,
		];

		// Add additional funds if any.
		if ( ! empty( $campaign['additional_funds'] ) ) {
			$atts['campaign_stats']['total'] += $campaign['additional_funds'];
		}

		return $this->twig->render( self::FORM_TEMPLATE, $atts );
	}
}
