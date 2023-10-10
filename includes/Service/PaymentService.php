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

use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Helper\Utils;
use Psr\Log\LoggerInterface;

class PaymentService extends AbstractService {
	private MailerService $mailer_service;
	private LoggerInterface $logger;
	private SettingsService $settings;

	/**
	 * Payment service constructor.
	 *
	 * @see https://stackoverflow.com/questions/36853791/laravel-dynamic-dependency-injection-for-interface-based-on-user-input
	 *
	 * @param MailerService   $mailer_service Mailer service.
	 * @param LoggerInterface $logger Logger.
	 * @param SettingsService $settings Settings service.
	 */
	public function __construct(
		MailerService $mailer_service,
		LoggerInterface $logger,
		SettingsService $settings
	) {
		$this->settings       = $settings;
		$this->logger         = $logger;
		$this->mailer_service = $mailer_service;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// First hook is to schedule the process hook once payment completed.
		add_action( 'kudos_transaction_paid', [ $this, 'schedule_process_transaction' ] );
		// Second hook is to call another hook that runs when scheduled hook called.
		add_action( 'kudos_process_transaction', [ $this, 'process_transaction' ] );
		// Replace returned get_home_url with app_url if defined.
		add_filter( 'rest_url', [ $this, 'use_alternate_app_url' ], 1, 2 );
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
	 * @param string $transaction_id The post id of the transaction.
	 */
	public static function schedule_process_transaction( string $transaction_id ): void {
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

		if ( get_post_meta( $donor->ID, DonorPostType::META_FIELD_EMAIL ) ) {
			// Send email - email setting is checked in mailer.
			$mailer->send_receipt( $donor->ID, $transaction_id );
		}

		return true;
	}
}
