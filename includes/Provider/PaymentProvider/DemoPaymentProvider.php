<?php
/**
 * Demo payment provider for use in KUDOS_DEMO_MODE (e.g. WordPress Playground).
 * No real API calls are made; a simple local checkout page simulates the payment flow.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Service\NoticeService;
use WP_REST_Request;
use WP_REST_Response;

class DemoPaymentProvider extends AbstractPaymentProvider implements ActivationAwareInterface {

	protected TransactionRepository $transaction_repository;

	/**
	 * DemoPaymentProvider constructor.
	 *
	 * @param TransactionRepository $transaction_repository The transaction repository.
	 */
	public function __construct( TransactionRepository $transaction_repository ) {
		$this->transaction_repository = $transaction_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation(): void {
		if ( KUDOS_DEMO_MODE ) {
			NoticeService::add_notice( __( 'Thanks for trying out Kudos Donations! Some settings can not be changed in demo mode.', 'kudos-donations' ), NoticeService::INFO, true, 'demo_mode', true, true );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'Demo';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'demo';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_icon_svg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 300 300"><path d="M0 0h300v300H0z" style="display:inline;fill:#2ec4b6;fill-opacity:1;stroke-width:.79375;paint-order:stroke fill markers"/><path d="M88.586 79.91a43 43 0 0 1 11.302-1.498h100.224c3.914 0 7.694.527 11.292 1.498a28.64 28.64 0 0 0-25.61-15.817h-71.589A28.64 28.64 0 0 0 88.586 79.91M56.934 150a28.636 28.636 0 0 1 28.636-28.636h128.86A28.636 28.636 0 0 1 243.066 150v57.271a28.636 28.636 0 0 1-28.636 28.636H85.57a28.636 28.636 0 0 1-28.636-28.636Zm28.636-42.953c-3.914 0-7.694.523-11.302 1.497a28.64 28.64 0 0 1 25.62-15.817h100.224a28.64 28.64 0 0 1 25.61 15.817 43 43 0 0 0-11.292-1.497z" style="fill:#fff;fill-opacity:1;stroke-width:9.54522"/></svg>';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * Registers the demo checkout and confirm REST routes.
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Registers the demo checkout REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'kudos/v1',
			'/payment/demo-checkout',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'demo_checkout' ],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			'kudos/v1',
			'/payment/demo-confirm',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'demo_confirm' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Outputs a simple HTML demo checkout page and exits.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function demo_checkout( WP_REST_Request $request ): void {
		$transaction_id = absint( $request->get_param( 'transaction_id' ) );
		$nonce          = (string) $request->get_param( 'kudos_nonce' );

		if ( ! hash_equals( wp_hash( 'kudos_demo_checkout_' . $transaction_id ), $nonce ) ) {
			wp_die( esc_html__( 'Invalid request.', 'kudos-donations' ) );
		}

		$transaction = $this->transaction_repository->get( $transaction_id );
		if ( ! $transaction ) {
			wp_die( esc_html__( 'Transaction not found.', 'kudos-donations' ) );
		}

		$return_url    = (string) $request->get_param( 'return_url' );
		$confirm_nonce = wp_hash( 'kudos_demo_confirm_' . $transaction_id );
		$confirm_url   = get_rest_url( null, 'kudos/v1/payment/demo-confirm' );

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		header( 'Content-Type: text/html; charset=utf-8' );
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width,initial-scale=1">
			<title><?php echo esc_html__( 'Demo Checkout', 'kudos-donations' ); ?></title>
			<style>
				body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f5f5f5}
				.card{background:#fff;border-radius:10px;border:1px solid #000;padding:2rem;max-width:400px;width:100%;text-align:center}
				h2{margin-top:0;color:#1d2327}
				.amount{font-size:2.2rem;font-weight:700;margin:1rem 0;color:#1d2327}
				.note{font-size:.88rem;color:#666;margin-bottom:1.5rem}
				button{background:#2271b1;color:#fff;border:none;padding:.75rem 1.5rem;border-radius:4px;font-size:1rem;cursor:pointer;width:100%}
				button:hover{background:#135e96}
				a{display:block;margin-top:1rem;color:#666;font-size:.9rem;text-decoration:none}
				a:hover{text-decoration:underline}
			</style>
		</head>
		<body>
		<div class="card">
			<h2><?php echo esc_html__( 'Demo Checkout', 'kudos-donations' ); ?></h2>
			<div class="amount"><?php echo esc_html( $transaction->currency ?? 'EUR' ); ?> <?php echo esc_html( number_format( $transaction->value, 2 ) ); ?></div>
			<p class="note"><?php echo esc_html__( 'This is a simulated payment. No real money will be transferred.', 'kudos-donations' ); ?></p>
			<form method="post" action="<?php echo esc_url( $confirm_url ); ?>">
				<input type="hidden" name="transaction_id" value="<?php echo absint( $transaction_id ); ?>">
				<input type="hidden" name="return_url" value="<?php echo esc_url( $return_url ); ?>">
				<input type="hidden" name="kudos_nonce" value="<?php echo esc_attr( $confirm_nonce ); ?>">
				<button type="submit"><?php echo esc_html__( 'Complete Demo Payment', 'kudos-donations' ); ?></button>
			</form>
			<a href="<?php echo esc_url( $return_url ); ?>"><?php echo esc_html__( 'Cancel', 'kudos-donations' ); ?></a>
		</div>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Marks the transaction as paid and redirects back to the return URL.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function demo_confirm( WP_REST_Request $request ): void {
		$transaction_id = absint( $request->get_param( 'transaction_id' ) );
		$nonce          = (string) $request->get_param( 'kudos_nonce' );

		if ( ! hash_equals( wp_hash( 'kudos_demo_confirm_' . $transaction_id ), $nonce ) ) {
			wp_die( esc_html__( 'Invalid request.', 'kudos-donations' ) );
		}

		$return_url  = (string) $request->get_param( 'return_url' );
		$transaction = $this->transaction_repository->get( $transaction_id );
		if ( $transaction && PaymentStatus::OPEN === $transaction->status ) {
			$transaction->status            = PaymentStatus::PAID;
			$transaction->vendor_payment_id = 'demo_pay_' . $transaction_id;
			$transaction->method            = 'demo';
			$transaction->mode              = 'test';
			$this->transaction_repository->update( $transaction );
			$this->on_transaction_status_changed( $transaction );
		}

		wp_safe_redirect( $return_url ? $return_url : get_site_url() );
		exit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_vendor_ready(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_status(): array {
		return [
			'ready'     => true,
			'recurring' => false,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_api_mode(): string {
		return 'test';
	}

	/**
	 * {@inheritDoc}
	 */
	public function refresh(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool {
		return (bool) $subscription;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Returns a stdClass with an id so the caller can store a vendor_customer_id.
	 */
	public function create_customer( string $email, string $name ) {
		$customer     = new \stdClass();
		$customer->id = 'demo_cust_' . substr( md5( $email . $name ), 0, 12 );
		return $customer;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Returns a URL to the local demo checkout page instead of a real payment gateway URL.
	 */
	public function create_payment( array $payment_args, TransactionEntity $transaction, ?string $vendor_customer_id = null ) {
		$this->logger->debug( 'Create payment', [ 'vendor_customer_id' => $vendor_customer_id ] );
		return add_query_arg(
			[
				'transaction_id' => $transaction->id,
				'return_url'     => $payment_args['return_url'] ? rawurlencode( $payment_args['return_url'] ) : get_site_url(),
				'kudos_nonce'    => wp_hash( 'kudos_demo_checkout_' . $transaction->id ),
			],
			get_rest_url( null, 'kudos/v1/payment/demo-checkout' )
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund( int $entity_id ): bool {
		return (bool) $entity_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rest_webhook( WP_REST_Request $request ): WP_REST_Response {
		$this->logger->debug( 'Rest webhook', [ 'request' => $request ] );
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not used in demo mode; completion is handled by the demo-confirm endpoint.
	 */
	public function handle_status_change( string $vendor_payment_id ): void {
		$this->logger->debug( 'Handle status change: ' . $vendor_payment_id );
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not used in demo mode; completion is handled by the demo-confirm endpoint.
	 */
	public function sync_transaction_status( int $transaction_id ): ?TransactionEntity {
		return $this->transaction_repository->get( $transaction_id );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_cache_setting(): ?string {
		return null;
	}
}
