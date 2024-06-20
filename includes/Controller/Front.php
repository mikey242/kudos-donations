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
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\AbstractService;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\VendorInterface;
use WP_REST_Server;

class Front extends AbstractService {
	private SettingsService $settings;
	private VendorInterface $vendor;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param SettingsService $settings Settings service.
	 * @param VendorInterface $vendor Payment vendors.
	 */
	public function __construct(
		SettingsService $settings,
		VendorInterface $vendor
	) {
		$this->settings = $settings;
		$this->vendor   = $vendor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_assets();
		$this->register_block();
		$this->register_shortcode();
		if ( $this->settings->get_setting( SettingsService::SETTING_NAME_ALWAYS_LOAD_ASSETS ) ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Register the assets needed to display Kudos.
	 */
	public function register_assets(): void {
		$public_js = Assets::get_script( 'front/kudos-front.js' );
		wp_register_style( 'kudos-donations-fonts', Assets::get_style( 'front/kudos-fonts.css' ), [], KUDOS_VERSION );
		wp_register_script(
			'kudos-donations-public',
			$public_js['url'],
			$public_js['dependencies'],
			$public_js['version'],
			true
		);

		wp_set_script_translations( 'kudos-donations-public', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );
	}

	/**
	 * Renders the kudos button and donation modals.
	 *
	 * @param array $args Array of Kudos button/modal attributes.
	 */
	public function kudos_render_callback( array $args ): ?string {

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

		// Create unique id for triggering.
		$id = Utils::generate_id( 'kudos-' );

		if ( 'button' === $args['type'] ) {
			// Add modal to footer.
			add_action(
				'wp_footer',
				function () use ( $args, $id ): void {
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $this->get_form_html( $id, $args );
				}
			);

			// Output button.
			return $this->get_button_html( $id, $args );
		}

		return $this->get_form_html( $id, $args );
	}

	/**
	 * Enqueue the styles and scripts.
	 */
	private function enqueue_assets(): void {
		wp_enqueue_style( 'kudos-donations-fonts' ); // Fonts need to be loaded in the main document.
		if ( ! has_block( 'iseardmedia/kudos-button' ) ) {
			wp_enqueue_script( 'kudos-donations-public' );
		}
	}

	/**
	 * Returns the html for the kudos form.
	 *
	 * @param string $id ID to use for the form.
	 * @param array  $args Attributes.
	 */
	private function get_form_html( string $id, array $args ): string {
		return wp_kses(
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
	private function get_button_html( string $id, array $args ): string {
		return wp_kses(
			wp_sprintf(
				"<p><span id='button-$id' class='kudos-button' data-label='%s' data-target='form-%s' data-campaign='%s'></span></p>",
				$args['button_label'],
				$id,
				$args['campaign_id']
			),
			[
				'p'    => [],
				'span' => [
					'id'            => [],
					'class'         => [],
					'data-label'    => [],
					'data-target'   => [],
					'data-campaign' => [],
				],
			]
		);
	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables(): void {
		if ( isset( $_REQUEST['kudos_action'] ) && -1 !== $_REQUEST['kudos_action'] ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );

			// Enqueue script / style in case we are on another page.
			$this->enqueue_assets();

			switch ( $action ) {
				case 'order_complete':
					$nonce          = sanitize_text_field( wp_unslash( $_REQUEST['kudos_nonce'] ) );
					$transaction_id = sanitize_text_field( $_REQUEST['kudos_transaction_id'] );
					// Return message modal.
					if ( ! empty( $transaction_id ) && ! empty( $nonce ) ) {
						$transaction = get_post( $transaction_id );

						if ( $transaction && wp_verify_nonce( $nonce, $action . $transaction_id ) ) {
							$campaign_id = $transaction->{TransactionPostType::META_FIELD_CAMPAIGN_ID};
							$campaign    = get_post( $campaign_id );

							$return_message_title  = $campaign->{CampaignPostType::META_FIELD_RETURN_MESSAGE_TITLE};
							$return_message_text   = $campaign->{CampaignPostType::META_FIELD_RETURN_MESSAGE_TEXT};
							$result                = [];
							$result['theme_color'] = $campaign->{CampaignPostType::META_FIELD_THEME_COLOR};
							$status                = $transaction->{TransactionPostType::META_FIELD_STATUS};

							switch ( $status ) {
								case PaymentStatus::PAID:
									$donor_id              = $transaction->{TransactionPostType::META_FIELD_DONOR_ID};
									$donor                 = get_post( $donor_id );
									$value                 = $transaction->{TransactionPostType::META_FIELD_VALUE};
									$currency              = $transaction->{TransactionPostType::META_FIELD_VALUE};
									$vars                  = [
										'{{value}}' => ( ! empty( $currency ) ? html_entity_decode(
											Utils::get_currency_symbol( $currency )
										) : '' ) . number_format_i18n(
											$value,
											2
										),
										'{{name}}'  => $donor->{DonorPostType::META_FIELD_NAME},
										'{{email}}' => $donor->{DonorPostType::META_FIELD_EMAIL},
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
					$id      = sanitize_text_field( wp_unslash( $_REQUEST['id'] ) );
					$token   = sanitize_text_field( wp_unslash( $_REQUEST['token'] ) );
					$request = new \WP_REST_Request( WP_REST_Server::READABLE, '/kudos/v1/subscription/cancel' );
					$request->set_query_params(
						[
							'id'    => $id,
							'token' => $token,
						]
					);
					$response = rest_do_request( $request );
					$data     = $response->get_data();
						$this->message_modal_html(
							$data['message'],
							$response->is_error() ? __( 'Please contact support.', 'kudos-donations' ) : __( 'Thanks for your support!', 'kudos-donations' )
						);
					break;
				default:
					$this->message_modal_html(
						'Unknown',
						'Unknown action supplied.',
					);
			}
		}
	}

	/**
	 * Register the Kudos button block.
	 */
	private function register_block(): void {
		$block = register_block_type(
			KUDOS_PLUGIN_DIR . '/build/front/button/',
			[
				'render_callback' => [ $this, 'kudos_render_callback' ],
			]
		);

		$handle = $block->script_handles[0];

		wp_enqueue_style( 'kudos-donations-fonts' ); // Fonts need to be loaded in the main document.
		wp_localize_script(
			$handle,
			'kudos',
			[
				'stylesheets' => [
					Assets::get_style( 'front/kudos-front.css' ),
				],
			]
		);
	}

	/**
	 * Register the kudos button shortcode.
	 */
	private function register_shortcode(): void {
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

				// Enqueue necessary resources.
				$this->enqueue_assets();

				return $this->kudos_render_callback( $args );
			}
		);

		/**
		 * Add the required stylesheets. Only needed for shortcode.
		 */
		wp_localize_script(
			'kudos-donations-public',
			'kudos',
			[
				'stylesheets' => [
					Assets::get_style( 'front/kudos-front.css' ),
				],
			]
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
