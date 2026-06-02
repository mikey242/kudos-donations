<?php
/**
 * Migration to add vendor column to kudos_donors and kudos_subscriptions tables.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\Table\CampaignsTable;
use IseardMedia\Kudos\Domain\Table\DonorsTable;
use IseardMedia\Kudos\Domain\Table\SubscriptionsTable;
use IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderInterface;

class Version4211 extends BaseMigration {

	protected string $version = '4.2.11';
	private DonorsTable $donors_table;
	private SubscriptionsTable $subscriptions_table;

	private PaymentProviderInterface $provider;

	/**
	 * @param DonorsTable            $donors_table        The donors table class.
	 * @param SubscriptionsTable     $subscriptions_table The subscriptions table class.
	 * @param PaymentProviderFactory $provider Used for fetching the current payment provider.
	 */
	public function __construct( DonorsTable $donors_table, SubscriptionsTable $subscriptions_table, PaymentProviderFactory $provider ) {
		$this->donors_table        = $donors_table;
		$this->subscriptions_table = $subscriptions_table;
		$this->provider            = $provider->get_provider();
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_auto(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_jobs(): array {
		return [
			'add_vendor_column_donors'        => $this->job( [ $this, 'add_vendor_column_donors' ], 'Adding vendor column to donors table', false ),
			'backfill_vendor_donors'          => $this->job( [ $this, 'backfill_vendor_donors' ], 'Backfilling vendor column for existing donors' ),
			'add_vendor_column_subscriptions' => $this->job( [ $this, 'add_vendor_column_subscriptions' ], 'Adding vendor column to subscriptions table', false ),
			'backfill_vendor_subscriptions'   => $this->job( [ $this, 'backfill_vendor_subscriptions' ], 'Backfilling vendor column for existing subscriptions' ),
			'backfill_duration_options'       => $this->job( [ $this, 'backfill_duration_options' ], 'Backfilling duration options for existing campaigns', false ),
			'refresh_payment_provider'        => $this->job( [ $this, 'refresh_payment_provider' ], 'Refreshing payment provider cache', false ),
		];
	}

	/**
	 * Runs dbDelta on the donors table to add the vendor column.
	 */
	public function add_vendor_column_donors(): void {
		$this->donors_table->create_table();
		$this->logger->info( 'Added vendor column to kudos_donors table.' );
	}

	/**
	 * Backfills the vendor column for existing donor rows.
	 *
	 * @param int $limit Number of rows to process per chunk.
	 * @return int Number of rows updated; 0 when complete.
	 */
	public function backfill_vendor_donors( int $limit ): int {
		global $wpdb;
		return $this->backfill_table( $wpdb->prefix . DonorsTable::get_name(), $limit );
	}

	/**
	 * Runs dbDelta on the subscriptions table to add the vendor column.
	 */
	public function add_vendor_column_subscriptions(): void {
		$this->subscriptions_table->create_table();
		$this->logger->info( 'Added vendor column to kudos_subscriptions table.' );
	}

	/**
	 * Backfills the vendor column for existing subscription rows.
	 *
	 * @param int $limit Number of rows to process per chunk.
	 * @return int Number of rows updated; 0 when complete.
	 */
	public function backfill_vendor_subscriptions( int $limit ): int {
		global $wpdb;
		return $this->backfill_table( $wpdb->prefix . SubscriptionsTable::get_name(), $limit );
	}

	/**
	 * Refresh the current payment provider's cache.
	 */
	public function refresh_payment_provider(): void {
		$this->provider->refresh();
	}

	/**
	 * Sets the default duration_options on campaigns that have a NULL value.
	 */
	public function backfill_duration_options(): void {
		global $wpdb;

		$table = $wpdb->prefix . CampaignsTable::get_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT id FROM %i WHERE duration_options IS NULL OR duration_options = ''", $table )
		);

		if ( empty( $ids ) ) {
			return;
		}

		$default = wp_json_encode(
			[
				'0'  => __( 'Continuous', 'kudos-donations' ),
				'1'  => __( '1 year', 'kudos-donations' ),
				'2'  => __( '2 years', 'kudos-donations' ),
				'3'  => __( '3 years', 'kudos-donations' ),
				'4'  => __( '4 years', 'kudos-donations' ),
				'5'  => __( '5 years', 'kudos-donations' ),
				'6'  => __( '6 years', 'kudos-donations' ),
				'7'  => __( '7 years', 'kudos-donations' ),
				'8'  => __( '8 years', 'kudos-donations' ),
				'9'  => __( '9 years', 'kudos-donations' ),
				'10' => __( '10 years', 'kudos-donations' ),
			],
			JSON_FORCE_OBJECT
		);

		$safe_ids = implode( ',', array_map( 'intval', $ids ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE %i SET duration_options = %s WHERE id IN ($safe_ids)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$table,
				$default
			)
		);

		$this->logger->info( 'Backfilled duration_options for ' . \count( $ids ) . ' campaigns.' );
	}

	/**
	 * Sets vendor = 'mollie' on all NULL-vendor rows in the given table.
	 *
	 * @param string $table Fully-qualified table name (with prefix).
	 * @param int    $limit Chunk size.
	 * @return int Rows updated; 0 when done.
	 */
	private function backfill_table( string $table, int $limit ): int {
		global $wpdb;

		$slug = MolliePaymentProvider::get_slug();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ids = $wpdb->get_col(
			$wpdb->prepare( 'SELECT id FROM %i WHERE vendor IS NULL LIMIT %d', $table, $limit )
		);

		if ( empty( $ids ) ) {
			return 0;
		}

		$safe_ids = implode( ',', array_map( 'intval', $ids ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE %i SET vendor = %s WHERE id IN ($safe_ids)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$table,
				$slug
			)
		);

		$this->logger->info( "Backfilled vendor='{$slug}' for " . \count( $ids ) . " rows in {$table}." );

		return \count( $ids );
	}
}
