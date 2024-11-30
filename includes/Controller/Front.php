<?php
/**
 * Front related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorInterface;
use WP_REST_Request;
use WP_REST_Server;

class Front extends AbstractRegistrable implements HasSettingsInterface {
	public const SETTING_ALWAYS_LOAD_ASSETS = '_kudos_always_load_assets';
	private PaymentVendorInterface $vendor;
	private array $view_script_handles = [];
	private array $style_handles;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param PaymentVendorInterface $vendor Payment vendors.
	 */
	public function __construct( PaymentVendorInterface $vendor ) {
		$this->vendor = $vendor;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'init';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_block();
		$this->register_shortcode();
		if ( get_option( self::SETTING_ALWAYS_LOAD_ASSETS ) ) {
			$this->enqueue_assets();
		}
		add_action( 'wp_footer', [ $this, 'handle_query_variables' ], 1 );
	}

	/**
	 * Enqueue the styles and scripts.
	 */
	private function enqueue_assets(): void {
		foreach ( $this->style_handles as $handle ) {
			wp_enqueue_style( $handle );
		}
		foreach ( $this->view_script_handles as $handle ) {
			wp_enqueue_script( $handle );
		}
	}

	/**
	 * Register the Kudos block.
	 */
	private function register_block(): void {
		$block = register_block_type(
			KUDOS_PLUGIN_DIR . '/build/front/block',
			[
				'render_callback' => [ $this, 'kudos_render_callback' ],
			]
		);

		// Update handle properties.
		$this->view_script_handles = $block->view_script_handles;
		$this->style_handles       = $block->style_handles;
		$block_scripts             = array_merge( $block->editor_script_handles, $block->view_script_handles );

		// Localize the scripts with required properties.
		foreach ( $block_scripts as $handle ) {
			wp_localize_script(
				$handle,
				'kudos',
				[
					'stylesheets'  => [
						Assets::get_style( 'front/block/kudos-front.css' ),
					],
					'currencies'   => Utils::get_currencies(),
					'baseFontSize' => get_option( SettingsService::SETTING_BASE_FONT_SIZE ),
				]
			);
		}
	}

	/**
	 * Register the kudos shortcode.
	 */
	private function register_shortcode(): void {
		add_shortcode(
			'kudos',
			function ( $args ) {
				$args = shortcode_atts(
					[
						'button_label' => __( 'Donate now', 'kudos-donations' ),
						'campaign_id'  => '',
						'type'         => 'button',
					],
					$args,
					'kudos'
				);
				$this->enqueue_assets();
				return $this->kudos_render_callback( $args );
			}
		);
	}

	/**
	 * Renders the kudos button and donation modals.
	 *
	 * @param array $args Array of Kudos button/modal attributes.
	 */
	public function kudos_render_callback( array $args ): ?string {
		do_action( 'kudos_render_callback', $args );

		// Check if the Campaign ID has been given.
		if ( empty( $args['campaign_id'] ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$message = __( 'No Campaign ID specified.', 'kudos-donations' );

				return '<p style="color: red; padding: 1em 0; font-weight: bold">' . $message . '</p>';
			}
		}

		// Check if the current vendor is connected.
		if ( ! $this->vendor->is_ready() ) {
			if ( current_user_can( 'manage_options' ) ) {
				$message = sprintf(
				/* translators: %s: Payment vendor (e.g. Mollie). */
					__( '%s not connected.', 'kudos-donations' ),
					$this->vendor::get_name()
				);
				return '<p style="color: red; padding: 1em 0; font-weight: bold">' . $message . '</p>';
			}
		}

		// Create unique id for triggering.
		$id         = Utils::generate_id( 'kudos-' );
		$attributes = wp_json_encode( $args );

		return wp_kses(
			wp_sprintf(
				"<p><span id='$id' class='kudos-form' data-options='$attributes'></span></p>",
				$args['type'],
				$args['button_label'],
				$id,
				$args['campaign_id']
			),
			[
				'p'    => [],
				'span' => [
					'id'           => [],
					'class'        => [],
					'data-options' => [],
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
					$nonce          = isset( $_REQUEST['kudos_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['kudos_nonce'] ) ) : '';
					$transaction_id = isset( $_REQUEST['kudos_transaction_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['kudos_transaction_id'] ) ) : '';
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
									$currency              = $transaction->{TransactionPostType::META_FIELD_CURRENCY};
									$currency_symbol       = Utils::get_currencies()[ $currency ] ?? null;
									$vars                  = [
										'{{value}}' => ( ! empty( $currency_symbol ) ? html_entity_decode(
											$currency_symbol
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
					$id      = isset( $_REQUEST['id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : '';
					$token   = isset( $_REQUEST['token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ) : '';
					$request = new WP_REST_Request( WP_REST_Server::READABLE, '/kudos/v1/subscription/cancel' );
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

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_ALWAYS_LOAD_ASSETS => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
		];
	}
}
