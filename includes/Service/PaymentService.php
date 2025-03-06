<?php
/**
 * Payment related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class PaymentService extends AbstractRegistrable implements HasSettingsInterface {
	public const SETTING_VENDOR = '_kudos_payment_vendor';
	private MailerService $mailer_service;
	private InvoiceService $invoice;

	/**
	 * Payment service constructor.
	 *
	 * @see https://stackoverflow.com/questions/36853791/laravel-dynamic-dependency-injection-for-interface-based-on-user-input
	 *
	 * @param MailerService  $mailer_service Mailer service.
	 * @param InvoiceService $invoice Invoice service.
	 */
	public function __construct( MailerService $mailer_service, InvoiceService $invoice ) {
		$this->mailer_service = $mailer_service;
		$this->invoice        = $invoice;
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
		// Automatically generates post title and content for all Kudos posts.
		add_action( 'kudos_post_saved', [ $this, 'on_kudos_post_saved' ], 10, 2 );
	}

	/**
	 * Update post title and content.
	 *
	 * @param int $post_id The id of the post.
	 */
	public function on_kudos_post_saved( int $post_id ) {
		$post        = get_post( $post_id );
		$object_type = get_post_type_object( get_post_type( $post_id ) );
		$postarr     = [
			'ID' => $post_id,
		];

		switch ( $object_type->name ) {
			case TransactionPostType::get_slug():
				$campaign_id        = $post->{TransactionPostType::META_FIELD_CAMPAIGN_ID} ?? '';
				$donor_id           = $post->{TransactionPostType::META_FIELD_DONOR_ID} ?? '';
				$donor              = get_post( $donor_id );
				$campaign           = get_post( $campaign_id );
				$description_format = $campaign->{CampaignPostType::META_PAYMENT_DESCRIPTION_FORMAT} ?? '';

				$vars                 = [];
				$vars['{{order_id}}'] = Utils::get_formatted_id( $post->ID );
				$vars['{{type}}']     = $post->{TransactionPostType::META_FIELD_SEQUENCE_TYPE} ?? '';

				// Add donor variables if available.
				if ( $donor ) {
					$vars['{{donor_name}}']  = $donor->{DonorPostType::META_FIELD_NAME} ?? '';
					$vars['{{donor_email}}'] = $donor->{DonorPostType::META_FIELD_EMAIL} ?? '';
				}

				// Add campaign variables if available.
				if ( $campaign ) {
					$vars['{{campaign_name}}'] = $campaign->post_title;
				}

				// Post content ready.
				$postarr['post_content'] = implode( ', ', $vars );

				// Generate title.
				$postarr['post_title'] = apply_filters(
					'kudos_payment_description',
					strtr( $description_format, $vars ),
					$post->{TransactionPostType::META_FIELD_SEQUENCE_TYPE},
					$post->ID,
					get_post( $post->{TransactionPostType::META_FIELD_CAMPAIGN_ID} )->post_title ?? '',
				);
				break;
			default:
				$single_name           = $object_type->labels->singular_name;
				$postarr['post_title'] = $single_name . \sprintf( ' (%1$s)', Utils::get_formatted_id( $post_id ) );
		}

		$this->logger->debug( 'Updating Kudos post', [ 'postarr' => $postarr ] );

		wp_update_post( $postarr );
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

		$current = (int) get_option( InvoiceService::SETTING_INVOICE_NUMBER );

		if ( update_post_meta( $transaction_id, TransactionPostType::META_FIELD_INVOICE_NUMBER, $current ) ) {
			update_option( InvoiceService::SETTING_INVOICE_NUMBER, ( $current + 1 ) );
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
			return str_replace( get_home_url(), sanitize_url( $_ENV['APP_URL'] ), $url );
		}
		return $url;
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
		$this->logger->debug( 'Processing paid transaction.', [ 'transaction_id' => $transaction_id ] );

		// Generate invoice.
		$this->invoice->generate_invoice( $transaction_id );

		// Get donor.
		$donor = get_post( get_post_meta( $transaction_id, TransactionPostType::META_FIELD_DONOR_ID, true ) );

		if ( $donor->{DonorPostType::META_FIELD_EMAIL} ) {
			// Send email - email setting is checked in mailer.
			$this->mailer_service->send_receipt( $donor->ID, $transaction_id );
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_VENDOR => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'mollie',
			],
		];
	}
}
