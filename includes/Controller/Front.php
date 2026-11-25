<?php
/**
 * Front related functions.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Localization;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Service\SettingsService;
use WP_REST_Request;
use WP_REST_Server;

class Front extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_ALWAYS_LOAD_ASSETS = '_kudos_always_load_assets';
	/**
	 * URL of the first public page a donation form was seen on. Set once; non-empty means
	 * the "Add to your site" onboarding step is done.
	 */
	public const SETTING_FORM_PLACED_URL = '_kudos_form_placed_url';
	public const STYLE_HANDLE_VIEW       = 'kudos-fonts';
	public const SCRIPT_HANDLE_VIEW      = 'kudos-view-script';
	public const SCRIPT_HANDLE_EDITOR    = 'kudos-editor-script';
	public const SCRIPT_HANDLES          = [
		self::SCRIPT_HANDLE_VIEW,
		self::SCRIPT_HANDLE_EDITOR,
	];
	private PaymentProviderFactory $factory;
	private TransactionRepository $transaction_repository;

	/**
	 * Script handles that have been given their window.kudos data.
	 *
	 * @var array<string, true>
	 */
	private array $localized     = [];
	private ?array $localization = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param PaymentProviderFactory $factory                Payment vendor factory.
	 * @param TransactionRepository  $transaction_repository Transaction repository.
	 */
	public function __construct( PaymentProviderFactory $factory, TransactionRepository $transaction_repository ) {
		$this->factory                = $factory;
		$this->transaction_repository = $transaction_repository;
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
		$this->register_assets();
		$this->register_block();
		$this->register_shortcode();
		if ( get_option( self::SETTING_ALWAYS_LOAD_ASSETS ) ) {
			$this->enqueue_assets();
		}
		add_action( 'wp_footer', [ $this, 'handle_query_variables' ], 1 );
	}

	/**
	 * Register the styles and scripts.
	 */
	private function register_assets(): void {
		// Styles.
		$font = Assets::get_style( 'front/kudos-fonts.css' );
		wp_register_style( self::STYLE_HANDLE_VIEW, $font, [], KUDOS_VERSION );

		// Scripts.
		$view = Assets::get_script( 'front/block/kudos-front.js' );
		if ( $view ) {
			wp_register_script(
				self::SCRIPT_HANDLE_VIEW,
				$view['url'],
				$view['dependencies'],
				$view['version'],
				true
			);
		}
		$edit = Assets::get_script( 'front/block/index.js' );
		if ( $edit ) {
			wp_register_script(
				self::SCRIPT_HANDLE_EDITOR,
				$edit['url'],
				$edit['dependencies'],
				$edit['version'],
				true
			);
		}
		foreach ( self::SCRIPT_HANDLES as $handle ) {
			wp_set_script_translations(
				$handle,
				'kudos-donations',
				\dirname( plugin_dir_path( __FILE__ ), 2 ) . '/languages'
			);
		}

		// Attach window.kudos only when one of our scripts is about to print, however it was
		// enqueued (block, shortcode, add-on), so pages without a form don't build the data.
		add_filter( 'print_scripts_array', [ $this, 'localize_scripts' ] );
	}

	/**
	 * Adds the window.kudos data to each Kudos script queued for printing, once per request.
	 *
	 * Hooked to print_scripts_array, which WP_Scripts applies before printing any script, so
	 * the data is in place before the script tag is output.
	 *
	 * @param string[] $handles Script handles about to be printed.
	 * @return string[]
	 */
	public function localize_scripts( $handles ) {
		foreach ( self::SCRIPT_HANDLES as $handle ) {
			if ( isset( $this->localized[ $handle ] ) || ! \in_array( $handle, (array) $handles, true ) ) {
				continue;
			}
			$this->localized[ $handle ] = true;
			wp_localize_script( $handle, 'kudos', $this->get_localization() );
		}

		return $handles;
	}

	/**
	 * Builds the front-end localization data, once per request.
	 */
	private function get_localization(): array {
		if ( null === $this->localization ) {
			// The front end calls the REST API with plain fetch(); rest_url() is right for every
			// permalink setting (with plain permalinks there is no /wp-json/ path).
			Localization::add_front( 'restRoot', esc_url_raw( rest_url() ) );
			Localization::add_front( 'stylesheets', [ Assets::get_style( 'front/block/kudos-front.css' ) ] );
			Localization::add_front( 'baseFontSize', get_option( SettingsService::SETTING_BASE_FONT_SIZE ) );

			$this->localization = Localization::get_front();
		}

		return $this->localization;
	}

	/**
	 * Enqueue the styles and scripts.
	 */
	private function enqueue_assets(): void {
		wp_enqueue_style( self::STYLE_HANDLE_VIEW );
		wp_enqueue_script( self::SCRIPT_HANDLE_VIEW );
	}

	/**
	 * Register the Kudos block.
	 */
	private function register_block(): void {
		$path = KUDOS_PLUGIN_DIR . '/build/front/block';
		if ( file_exists( $path ) ) {
			register_block_type(
				$path,
				[
					'render_callback' => [ $this, 'kudos_render_callback' ],
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
						'alignment'    => 'left',
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
			return null;
		}

		// Before the vendor check, so placing the form counts even before it is connected.
		$this->maybe_record_form_placement();

		// Check if the current vendor is connected.
		$vendor = $this->factory->get_active_provider();
		if ( null === $vendor || ! $vendor->is_vendor_ready() ) {
			if ( current_user_can( 'manage_options' ) ) {
				$message = null === $vendor
					? 'Kudos Donations: ' . __( 'No payment vendor configured.', 'kudos-donations' )
					: 'Kudos Donations: ' . \sprintf(
						/* translators: %s: Payment vendor (e.g. Mollie). */
						__( '%s not connected.', 'kudos-donations' ),
						$vendor::get_name()
					);

				return '<p style="color: red; padding: 1em 0; font-weight: bold">' . $message . '</p>';
			}
			return null;
		}

		// Create unique id for triggering.
		$id = Utils::generate_id( 'kudos-' );

		// Add Kudos Portal for mounting the modal.
		add_action( 'wp_footer', [ $this, 'kudos_portal_html' ] );

		// esc_attr() so a label with an apostrophe (e.g. "St John's") can't end the attribute.
		return \sprintf(
			'<p><span id="%s" class="kudos-form" data-options="%s"></span></p>',
			esc_attr( $id ),
			esc_attr( (string) wp_json_encode( $args ) )
		);
	}

	/**
	 * Renders the Kudos Portal.
	 */
	public function kudos_portal_html(): void {
		echo '<div id="kudos-portal"></div>';
	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- each case below performs its own token/hash verification.
		if ( isset( $_REQUEST['kudos_action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );

			// Enqueue script / style in case we are on another page.
			$this->enqueue_assets();

			switch ( $action ) {
				case 'order_complete':
					$nonce          = isset( $_REQUEST['kudos_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['kudos_nonce'] ) ) : '';
					$transaction_id = isset( $_REQUEST['kudos_transaction_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['kudos_transaction_id'] ) ) : '';

					// Return message modal.
					if ( ! empty( $transaction_id ) && ! empty( $nonce ) ) {
						/**
						 * @var ?TransactionEntity $transaction
						 */
						$transaction = $this->transaction_repository->get( (int) $transaction_id );

						if ( $transaction && $transaction->campaign_id && EncryptionService::verify_token( $action . $transaction_id, $nonce ) ) {
							$this->payment_status_modal_html(
								(int) $transaction_id,
								$transaction->campaign_id,
							);
						}
					}
					break;
				case 'cancel_subscription':
					$id      = isset( $_REQUEST['id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : '';
					$token   = isset( $_REQUEST['token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ) : '';
					$request = new WP_REST_Request( WP_REST_Server::CREATABLE, '/kudos/v1/subscription/cancel' );
					$request->set_body_params(
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
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Create message modal with supplied header and body text.
	 *
	 * @param string $header The header text.
	 * @param string $body The body text.
	 */
	private function message_modal_html( string $header, string $body ): void {
		// esc_attr() so a message with an apostrophe (common in translations) can't end the attribute.
		printf(
			'<div class="kudos-donations kudos-message" data-color="#ff9f1c" data-title="%s" data-body="%s"></div>',
			esc_attr( $header ),
			esc_attr( $body )
		);
	}

	/**
	 * Create message modal with supplied header and body text.
	 *
	 * @param int $transaction_id The transaction id.
	 * @param int $campaign_id The campaign id.
	 */
	private function payment_status_modal_html( int $transaction_id, int $campaign_id ): void {
		printf(
			'<div class="kudos-donations kudos-transaction-status" data-transaction="%d" data-campaign="%d"></div>',
			(int) $transaction_id,
			(int) $campaign_id
		);
	}

	/**
	 * Whether a donation form has been seen on a public page of the site.
	 */
	public static function is_form_placed(): bool {
		return '' !== (string) get_option( self::SETTING_FORM_PLACED_URL, '' );
	}

	/**
	 * Records the current page's URL the first time a form renders on the public site.
	 *
	 * Detecting this at render time rather than on save_post also catches forms placed by
	 * page builders, widgets, templates and theme code, which never appear in post_content.
	 * Admin screens, REST/AJAX, previews and feeds don't count: the form isn't live there.
	 */
	private function maybe_record_form_placement(): void {
		if ( self::is_form_placed() ) {
			return;
		}

		if ( is_admin() || wp_doing_ajax() || wp_is_serving_rest_request() || wp_is_json_request()
			|| is_preview() || is_customize_preview() || is_feed() || is_embed() ) {
			return;
		}

		global $wp;
		// The permalink on single posts/pages, the request path elsewhere; never the query
		// string, which may carry tracking or Kudos return parameters.
		$url = is_singular()
			? get_permalink( get_queried_object_id() )
			: home_url( user_trailingslashit( (string) ( $wp->request ?? '' ) ) );

		if ( $url ) {
			update_option( self::SETTING_FORM_PLACED_URL, esc_url_raw( $url ) );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_ALWAYS_LOAD_ASSETS => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::SETTING_FORM_PLACED_URL    => [
				'type'              => FieldType::STRING,
				'show_in_rest'      => true,
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			],
		];
	}
}
