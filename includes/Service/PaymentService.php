<?php
/**
 * Payment related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Helper\Utils;
use WP_Post;

class PaymentService extends AbstractRegistrable {
	private MailerService $mailer_service;
	private SettingsService $settings;

	/**
	 * Payment service constructor.
	 *
	 * @see https://stackoverflow.com/questions/36853791/laravel-dynamic-dependency-injection-for-interface-based-on-user-input
	 *
	 * @param MailerService   $mailer_service Mailer service.
	 * @param SettingsService $settings Settings service.
	 */
	public function __construct(
		MailerService $mailer_service,
		SettingsService $settings
	) {
		$this->settings       = $settings;
		$this->mailer_service = $mailer_service;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// Process paid transaction.
		add_action( 'kudos_transaction_paid', [ $this, 'handle_paid_transaction' ] );
		// Second hook is to call another hook that runs when scheduled hook called.
		add_action( 'kudos_process_transaction', [ $this, 'process_transaction' ] );
		// Replace returned get_home_url with app_url if defined.
		add_filter( 'rest_url', [ $this, 'use_alternate_app_url' ], 1, 2 );
		// Runs when new transactions are created.
		add_action( 'save_post_kudos_transaction', [ $this, 'add_description' ], 10, 3 );
	}

	/**
	 * Adds a description for new transactions.
	 *
	 * @param int     $post_id The id of the post.
	 * @param WP_Post $transaction The post object.
	 * @param bool    $update Whether this is an update or not.
	 */
	public function add_description( int $post_id, WP_Post $transaction, bool $update ) {
		// Bail immediately if this is an update.
		if ( $update ) {
			return;
		}

		$this->logger->debug( 'Updating post title', [ 'post_id' => $post_id ] );
		TransactionPostType::save(
			[
				'ID'         => $transaction->ID,
				'post_title' => apply_filters(
					'kudos_payment_description',
					__( 'Donation', 'kudos-donations' ) . sprintf( ' (%1$s)', TransactionPostType::get_formatted_id( $transaction->ID ) )
				),
			]
		);
	}

	/**
	 * Handle paid transactions.
	 *
	 * @param int $transaction_id The id of the paid transaction.
	 */
	public function handle_paid_transaction( int $transaction_id ) {
		// Updates the invoice number and iterates it for the next one.
		$this->iterate_invoice_number( $transaction_id );
		// Schedule the process hook once payment completed.
		$this->schedule_process_transaction( $transaction_id );
	}

	/**
	 * Adds the invoice number to a transaction and iterates it.
	 *
	 * @param int $transaction_id The id of the post being updated.
	 * @return void
	 */
	public function iterate_invoice_number( int $transaction_id ) {

		$current = (int) get_option( SettingsService::SETTING_NAME_INVOICE_NUMBER );

		if ( update_post_meta( $transaction_id, TransactionPostType::META_FIELD_INVOICE_NUMBER, $current ) ) {
			update_option( SettingsService::SETTING_NAME_INVOICE_NUMBER, ( $current + 1 ) );
		}
	}

	/**
	 * Replaces the home url with the url defined in $_ENV['APP_URL'].
	 *
	 * @param string $url The full URL.
	 * @param string $path The rest route.
	 */
	public function use_alternate_app_url( string $url, string $path ): string {
		if ( isset( $_ENV['APP_URL'] ) && '/kudos/v1/payment/webhook' === $path ) {
			return str_replace( get_home_url(), $_ENV['APP_URL'], $url );
		}
		return $url;
	}

	/**
	 * Checks if required api settings are saved before displaying button.
	 */
	public function is_api_ready(): bool {
		$settings  = $this->settings->get_current_vendor_settings();
		$mode      = $settings['mode'];
		$connected = $settings[ $mode . '_key' ]['verified'] ?? null;

		if ( ! $connected ) {
			return false;
		}

		return true;
	}

	/**
	 * Schedules processing of successful transaction.
	 *
	 * @param int $transaction_id The post id of the transaction.
	 */
	public function schedule_process_transaction( int $transaction_id ): void {
		Utils::schedule_action(
			strtotime( '+1 minute' ),
			'kudos_process_transaction',
			[ $transaction_id ]
		);
	}

	/**
	 * Processes the transaction. Used by action scheduler.
	 *
	 * @param int $transaction_id Transaction post id..
	 */
	public function process_transaction( int $transaction_id ): bool {
		$mailer = $this->mailer_service;

		$this->logger->debug( 'Processing paid transaction.', [ 'transaction_id' => $transaction_id ] );

		// Get donor.
		$donor = get_post( get_post_meta( $transaction_id, TransactionPostType::META_FIELD_DONOR_ID, true ) );

		if ( $donor->{DonorPostType::META_FIELD_EMAIL} ) {
			// Send email - email setting is checked in mailer.
			$mailer->send_receipt( $donor->ID, $transaction_id );
		}

		return true;
	}
}