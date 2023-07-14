<?php

namespace IseardMedia\Kudos\Controller;

use DI\NotFoundException;
use Exception;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Settings;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Infrastructure\Container\AbstractService;
use IseardMedia\Kudos\Service\PaymentService;
use Psr\Log\LoggerInterface;

class Front extends AbstractService {

	/**
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * @var PaymentService
	 */
	private PaymentService $payment;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct(
		LoggerInterface $logger,
		PaymentService $payment,
	) {
		$this->logger  = $logger;
		$this->payment = $payment;
	}

	public function register(): void {
		$this->register_kudos();
		add_action( 'wp_footer', [ $this, 'handle_query_variables' ], 1 );
	}

	/**
	 * Registers the button shortcode and block.
	 */
	public function register_kudos(): void {
		$this->register_assets();
		$this->register_blocks();
		$this->register_button_shortcode();
		if ( Settings::get_setting( 'always_load_assets' ) ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Register the assets needed to display Kudos.
	 *
	 * @throws NotFoundException
	 */
	public function register_assets(): void {
		$public_js = Assets::get_script( 'front/kudos-public.js' );
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
				'stylesheets' => [
					Assets::get_style( 'front/kudos-public.css' ),
				],
			]
		);

		wp_set_script_translations( 'kudos-donations-public', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );
	}

	/**
	 * Renders the kudos button and donation modals.
	 *
	 * @param array $atts Array of Kudos button/modal attributes.
	 */
	public function button_render_callback( array $atts ): ?string {
		try {
			// Check if the current vendor is connected, otherwise throw an exception.
			if ( ! $this->payment::is_api_ready() ) {
				/* translators: %s: Payment vendor (e.g. Mollie). */
				throw new Exception(
					sprintf(
						__( '%s not connected.', 'kudos-donations' ),
						$this->payment::get_vendor_name()
					)
				);
			}

			// Enqueue necessary resources.
			$this->enqueue_assets();

			// Create unique id for triggering.
			$id = Utils::generate_id( 'kudos-' );

			if ( $atts['type'] === 'button' ) {
				// Add modal to footer.
				add_action(
					'wp_footer',
					function () use ( $atts, $id ): void {
						echo $this->form_html( $id, $atts );
					}
				);

				// Output button.
				return $this->button_html( $id, $atts );
			}

			return $this->form_html( $id, $atts );
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
	 * Enqueue the styles and scripts.
	 */
	public function enqueue_assets(): void {
		wp_enqueue_script( 'kudos-donations-public' );
		wp_enqueue_style( 'kudos-donations-fonts' ); // Fonts need to be loaded in the main document.
	}

	public function form_html( $id, $atts ): string {
		return "<div id='form-$id' class='kudos-donations kudos-form' data-display-as='" . $atts['type'] . "' data-campaign='" . $atts['campaign_id'] . "' style='display: block'>
					</div>";
	}

	public function button_html( $id, $atts ): string {
		return "<div id='button-$id' class='button' data-label='" . $atts['button_label'] . "' data-target='form-$id' data-campaign='" . $atts['campaign_id'] . "' style='display: block'>
					</div>";
	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables(): void {
		if ( isset( $_REQUEST['kudos_action'] ) && -1 !== $_REQUEST['kudos_action'] ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$nonce  = sanitize_text_field( wp_unslash( $_REQUEST['kudos_nonce'] ) );

			// Enqueue script / style in case we are on another page.
			$this->enqueue_assets();

			switch ( $action ) {
				case 'order_complete':
					$order_id = sanitize_text_field( $_REQUEST['kudos_order_id'] );
					// Return message modal.
					if ( ! empty( $order_id ) && ! empty( $nonce ) ) {
						$transaction = TransactionPostType::get_by_meta(
							[
								'order_id' => $order_id,
							]
						)[0] ?? null;

						if ( $transaction && wp_verify_nonce( $nonce, $action . $order_id ) ) {
							$transaction_id = $transaction->ID;
							$campaign_id    = get_post_meta( $transaction_id, 'campaign_id', true );

							$campaign_meta         = get_post_meta( $campaign_id );
							$return_message_title  = get_post_meta( $campaign_id, 'return_message_title', true );
							$return_message_text   = get_post_meta( $campaign_id, 'return_message_text', true );
							$result                = [];
							$result['theme_color'] = $campaign_meta['theme_color'][0];
							$status                = get_post_meta( $transaction_id, 'status', true );

							switch ( $status ) {
								case PaymentStatus::PAID:
									$vars                  = [
										'{{value}}' => ( ! empty( $transaction->currency ) ? html_entity_decode(
											Utils::get_currency_symbol( $transaction->currency )
										) : '' ) . number_format_i18n(
											$transaction->value,
											2
										),
										'{{name}}'  => get_post_meta( $transaction_id, 'name', true ),
										'{{email}}' => get_post_meta( $transaction_id, 'email', true ),
									];
									$result['modal_title'] = strtr( $return_message_title, $vars );
									$result['modal_text']  = strtr( $return_message_text, $vars );
									break;
								case PaymentStatus::CANCELED:
									$result['modal_title'] = __( 'Payment cancelled', 'kudos-donations' );
									$result['modal_text']  = __(
										'You have not been charged for this transaction.',
										'kudos-donations'
									);
									break;
								default:
									$result['modal_title'] = __( 'Thanks', 'kudos-donations' );
									$result['modal_text']  = __(
										'Your donation will be processed soon.',
										'kudos-donations'
									);
									break;
							}

							if ( $result ) {
								echo $this->message_modal_html(
									$result['modal_title'],
									$result['modal_text'],
									$result['theme_color']
								);
							}
						}
					}
					break;

				case 'cancel_subscription':
					$subscription_id = sanitize_text_field( $_REQUEST['kudos_subscription_id'] );
					// Cancel subscription modal.
					if ( ! empty( $nonce && ! empty( $subscription_id ) ) ) {
						$subscription = SubscriptionPostType::get_by_meta(
							[
								'subscription_id' => $subscription_id,
							]
						);

						// Bail if no subscription found.
						if ( null === $subscription ) {
							break;
						}

						if ( wp_verify_nonce( $nonce, $action ) ) {
							if ( $this->payment->cancel_subscription( $subscription_id ) ) {
								echo $this->message_modal_html(
									__( 'Subscription cancelled', 'kudos-donations' ),
									__(
										'We will no longer be taking payments for this subscription. Thank you for your contributions.',
										'kudos-donations'
									)
								);

								break;
							}
						}

						echo $this->message_modal_html(
							__( 'Link expired', 'kudos-donations' ),
							__( 'Sorry, this link is no longer valid.', 'kudos-donations' )
						);
					}
					break;
			}
		}
	}

	/**
	 * Register the Kudos button block.
	 */
	private function register_blocks(): void {
		register_block_type(
			KUDOS_PLUGIN_DIR . '/build/front/button/',
			[
				'render_callback' => [ $this, 'button_render_callback' ],
			]
		);
	}

	/**
	 * Register the kudos button shortcode.
	 */
	private function register_button_shortcode(): void {
		// Register shortcode.
		add_shortcode(
			'kudos',
			function ( $atts ) {
				$atts = shortcode_atts(
					[
						'button_label' => __( 'Donate now', 'kudos-donations' ),
						'campaign_id'  => '',
						'alignment'    => 'none',
						'type'         => 'button',
					],
					$atts,
					'kudos'
				);

				return $this->button_render_callback( $atts );
			}
		);
	}

	/**
	 * Create message modal with supplied header and body text.
	 *
	 * @param string $header The header text.
	 * @param string $body The body text.
	 * @param string $color
	 */
	private function message_modal_html( string $header, string $body, string $color = '#ff9f1c' ): ?string {
		return "<div class='kudos-donations kudos-message' data-color='$color' data-title='$header' data-body='$body'></div>";
	}
}
