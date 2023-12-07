<?php
/**
 * Front related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\AbstractService;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\VendorInterface;

class Front extends AbstractService {
	private SettingsService $settings;
	private PaymentService $payment;
	private VendorInterface $vendor;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param PaymentService  $payment Payment service.
	 * @param SettingsService $settings Settings service.
	 * @param VendorInterface $vendor Payment vendors.
	 */
	public function __construct(
		PaymentService $payment,
		SettingsService $settings,
		VendorInterface $vendor
	) {
		$this->payment  = $payment;
		$this->settings = $settings;
		$this->vendor   = $vendor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_kudos();
		$this->register_assets();
		add_action( 'wp_footer', [ $this, 'handle_query_variables' ], 1 );
	}

	/**
	 * Registers the button shortcode and block.
	 */
	public function register_kudos(): void {
		$this->register_blocks();
		$this->register_button_shortcode();
		if ( $this->settings->get_setting( SettingsService::SETTING_NAME_ALWAYS_LOAD_ASSETS ) ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Register the assets needed to display Kudos.
	 */
	public function register_assets(): void {
		$public_js = Assets::get_script( 'front/kudos-front.js' );
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
					Assets::get_style( 'front/kudos-front.css' ),
				],
			]
		);

		wp_set_script_translations( 'kudos-donations-public', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );
	}

	/**
	 * Renders the kudos button and donation modals.
	 *
	 * @param array $args Array of Kudos button/modal attributes.
	 */
	public function button_render_callback( array $args ): ?string {

		// Check if the current vendor is connected, otherwise show an error to logged in admins.
		if ( ! $this->vendor->is_ready() ) {
			if ( current_user_can( 'manage_options' ) ) {
				$message = sprintf(
				/* translators: %s: Payment vendor (e.g. Mollie). */
					__( '%s not connected.', 'kudos-donations' ),
					$this->vendor::get_vendor_name()
				);
				return '<p style="color: red; padding: 1em 0; font-weight: bold">' . $message . '</p>';
			}
		}

		// Enqueue necessary resources.
		$this->enqueue_assets();

		// Create unique id for triggering.
		$id = Utils::generate_id( 'kudos-' );

		if ( 'button' === $args['type'] ) {
			// Add modal to footer.
			add_action(
				'wp_footer',
				function () use ( $args, $id ): void {
					$this->form_html( $id, $args );
				}
			);

			// Output button.
			return $this->button_html( $id, $args );
		}

			$this->form_html( $id, $args );

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

	/**
	 * Returns the html for the kudos form.
	 *
	 * @param string $id ID to use for the form.
	 * @param array  $args Attributes.
	 */
	public function form_html( string $id, array $args ): void {
		echo wp_kses(
			wp_sprintf(
				"<div id='form-%s' class='kudos-donations kudos-form' data-display-as='%s' data-campaign='%s' style='display: block'>
					</div>",
				$id,
				$args['type'],
				$args['campaign_id']
			),
			[
				'div' => [
					'id'              => [],
					'class'           => [],
					'data-display-as' => [],
					'data-campaign'   => [],
					'style'           => [],
				],
			]
		);
	}

	/**
	 * Returns the html for the kudos button.
	 *
	 * @param string $id ID to use for the form.
	 * @param array  $args Attributes.
	 */
	public function button_html( string $id, array $args ): string {
		return "<div id='button-$id' class='button' data-label='" . $args['button_label'] . "' data-target='form-$id' data-campaign='" . $args['campaign_id'] . "' style='display: block'>
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
					$transaction_id = sanitize_text_field( $_REQUEST['kudos_transaction_id'] );
					// Return message modal.
					if ( ! empty( $transaction_id ) && ! empty( $nonce ) ) {
						$transaction = get_post( $transaction_id );

						if ( $transaction && wp_verify_nonce( $nonce, $action . $transaction_id ) ) {
							$transaction_id = $transaction->ID;
							$campaign_id    = get_post_meta( $transaction_id, 'campaign_id', true );

							$return_message_title  = get_post_meta( $campaign_id, CampaignPostType::META_FIELD_RETURN_MESSAGE_TITLE, true );
							$return_message_text   = get_post_meta( $campaign_id, CampaignPostType::META_FIELD_RETURN_MESSAGE_TEXT, true );
							$result                = [];
							$result['theme_color'] = get_post_meta( $campaign_id, CampaignPostType::META_FIELD_THEME_COLOR, true );
							$status                = get_post_meta( $transaction_id, 'status', true );

							switch ( $status ) {
								case PaymentStatus::PAID:
									$donor_id              = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_DONOR_ID, true );
									$value                 = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_VALUE, true );
									$currency              = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_CURRENCY, true );
									$vars                  = [
										'{{value}}' => ( ! empty( $currency ) ? html_entity_decode(
											Utils::get_currency_symbol( $currency )
										) : '' ) . number_format_i18n(
											$value,
											2
										),
										'{{name}}'  => get_post_meta( $donor_id, DonorPostType::META_FIELD_NAME, true ),
										'{{email}}' => get_post_meta( $donor_id, DonorPostType::META_FIELD_EMAIL, true ),
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
								$this->message_modal_html(
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
						$subscription = SubscriptionPostType::get_post(
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
								$this->message_modal_html(
									__( 'Subscription cancelled', 'kudos-donations' ),
									__(
										'We will no longer be taking payments for this subscription. Thank you for your contributions.',
										'kudos-donations'
									)
								);

								break;
							}
						}

						$this->message_modal_html(
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
			function ( $args ) {
				$args = shortcode_atts(
					[
						'button_label' => __( 'Donate now', 'kudos-donations' ),
						'campaign_id'  => '',
						'alignment'    => 'none',
						'type'         => 'button',
					],
					$args,
					'kudos'
				);

				return $this->button_render_callback( $args );
			}
		);
	}

	/**
	 * Create message modal with supplied header and body text.
	 *
	 * @param string $header The header text.
	 * @param string $body The body text.
	 * @param string $color The theme color.
	 */
	private function message_modal_html( string $header, string $body, string $color = '#ff9f1c' ): void {
		echo wp_kses(
			wp_sprintf( "<div class='kudos-donations kudos-message' data-color='%s' data-title='%s' data-body='%s'></div>", $color, $header, $body ),
			[
				'div' => [
					'class'      => [],
					'data-color' => [],
					'data-title' => [],
					'data-body'  => [],
				],
			]
		);
	}
}
