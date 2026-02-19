<?php
/**
 * Migration to create the custom data tables.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 *
 * @phpcs:disable Universal.Operators.DisallowShortTernary.Found
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\BaseRepository;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Domain\Table\BaseTable;
use IseardMedia\Kudos\Domain\Table\CampaignsTable;
use IseardMedia\Kudos\Domain\Table\DonorsTable;
use IseardMedia\Kudos\Domain\Table\SubscriptionsTable;
use IseardMedia\Kudos\Domain\Table\TransactionsTable;
use IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
use IseardMedia\Kudos\Service\LinkService;
use WP_Post;

class Version420 extends BaseMigration implements RepositoryAwareInterface {

	use RepositoryAwareTrait;

	protected string $version = '4.2.0';
	private MolliePaymentProvider $mollie;
	/**
	 * @var BaseTable[]
	 */
	private array $tables;
	private LinkService $link_service;

	/**
	 * Add MolliePaymentVendor for handling refresh and add tables for creation.
	 *
	 * @param MolliePaymentProvider $mollie_payment_vendor Mollie related functions.
	 * @param CampaignsTable        $campaigns_table The campaigns table class.
	 * @param DonorsTable           $donors_table The donors table class.
	 * @param TransactionsTable     $transactions_table The transactions table class.
	 * @param SubscriptionsTable    $subscriptions_table The subscriptions table class.
	 * @param LinkService           $link_service Reconnect entities via their vendor ids.
	 */
	public function __construct( MolliePaymentProvider $mollie_payment_vendor, CampaignsTable $campaigns_table, DonorsTable $donors_table, TransactionsTable $transactions_table, SubscriptionsTable $subscriptions_table, LinkService $link_service ) {
		$this->mollie       = $mollie_payment_vendor;
		$this->tables       = [
			$campaigns_table,
			$donors_table,
			$transactions_table,
			$subscriptions_table,
		];
		$this->link_service = $link_service;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_jobs(): array {
		return [
			'prepare_tables'                  => $this->job( [ $this, 'prepare_tables' ], 'Preparing tables', false ),
			'donors'                          => $this->job( [ $this, 'migrate_donors' ], 'Migrating donors' ),
			'campaigns'                       => $this->job( [ $this, 'migrate_campaigns' ], 'Migrating campaigns' ),
			'transactions'                    => $this->job( [ $this, 'migrate_transactions' ], 'Migrating transactions' ),
			'subscriptions'                   => $this->job( [ $this, 'migrate_subscriptions' ], 'Migrating subscriptions' ),
			'backfill_transactions'           => $this->job( [ $this, 'backfill_transactions_from_subscription' ], 'Add subscription id to transactions' ),
			'backfill_remaining_transactions' => $this->job( [ $this, 'backfill_remaining_transactions' ], 'Add subscription id to transactions' ),
			'relink_donors_to_transactions'   => $this->job( [ $this, 'relink_donors_to_transactions' ], 'Re-linking donors to transactions' ),
			'relink_donors_to_subscriptions'  => $this->job( [ $this, 'relink_donors_to_subscriptions' ], 'Re-linking donors to subscriptions' ),
			'refresh_mollie'                  => $this->job( [ $this, 'refresh_mollie_status' ], 'Refresh Mollie status', false ),
		];
	}

	/**
	 * Drops old-schema tables and ensures new-schema tables exist.
	 *
	 * Only drops a table if it exists with the old schema (lacks the wp_post_id column).
	 * Tables already created by this migration (with wp_post_id) are left intact.
	 * dbDelta handles creating missing tables or adding missing columns.
	 */
	public function prepare_tables(): void {
		foreach ( $this->tables as $table ) {
			if ( ! $table->has_column( 'wp_post_id' ) ) {
				$table->drop_table();
			}
			$table->create_table();
		}
	}

	/**
	 * Migrates kudos_donor CPTs to the kudos_donors table in chunks.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_donors( int $limit ): int {
		$post_type  = 'kudos_donor';
		$donor_repo = $this->get_repository( DonorRepository::class );

		$ids = $this->get_unmigrated_posts( $donor_repo, $post_type, $limit );

		if ( empty( $ids ) ) {
			$this->logger->info( 'No more donors to migrate.' );
			return 0;
		}

		foreach ( $ids as $post_id ) {
			$donor = new DonorEntity(
				[
					'wp_post_id'         => $post_id,
					'title'              => get_post_field( 'post_title', $post_id ),
					'name'               => get_post_meta( $post_id, 'name', true ),
					'email'              => get_post_meta( $post_id, 'email', true ),
					'mode'               => get_post_meta( $post_id, 'mode', true ),
					'business_name'      => get_post_meta( $post_id, 'business_name', true ),
					'street'             => get_post_meta( $post_id, 'street', true ),
					'postcode'           => get_post_meta( $post_id, 'postcode', true ),
					'city'               => get_post_meta( $post_id, 'city', true ),
					'country'            => get_post_meta( $post_id, 'country', true ),
					'vendor_customer_id' => get_post_meta( $post_id, 'vendor_customer_id', true ),
					'created_at'         => get_post_time( 'Y-m-d H:i:s', true, $post_id ),
					'updated_at'         => get_post_modified_time( 'Y-m-d H:i:s', true, $post_id ),
				]
			);

			$result = $donor_repo->insert( $donor );
			if ( false === $result ) {
				$this->logger->error( "Failed to insert donor post $post_id", [ 'db_error' => $donor_repo->get_last_db_error() ] );
			} else {
				$this->logger->info( "Migrated donor post $post_id", [ 'id' => $result ] );
			}
		}

		return \count( $ids );
	}

	/**
	 * Migrates kudos_campaign CPTs to the kudos_campaigns table in chunks.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_campaigns( int $limit ): int {
		$post_type     = 'kudos_campaign';
		$campaign_repo = $this->get_repository( CampaignRepository::class );

		$ids = $this->get_unmigrated_posts( $campaign_repo, $post_type, $limit );

		if ( empty( $ids ) ) {
			$this->logger->info( 'No more campaigns to migrate.' );
			return 0;
		}

		foreach ( $ids as $post_id ) {
			$campaign = new CampaignEntity(
				[
					'wp_post_id'                 => $post_id,
					'wp_post_slug'               => get_the_title( $post_id ),
					'title'                      => get_post_field( 'post_title', $post_id ),
					'currency'                   => get_post_meta( $post_id, 'currency', true ) ?: 'EUR',
					'goal'                       => $this->get_meta_float( $post_id, 'goal' ),
					'show_goal'                  => $this->get_meta_bool( $post_id, 'show_goal', false ),
					'additional_funds'           => $this->get_meta_float( $post_id, 'additional_funds' ),
					'amount_type'                => get_post_meta( $post_id, 'amount_type', true ),
					'fixed_amounts'              => get_post_meta( $post_id, 'fixed_amounts', true ) ?: [],
					'minimum_donation'           => $this->get_meta_float( $post_id, 'minimum_donation', 1.0 ),
					'maximum_donation'           => $this->get_meta_float( $post_id, 'maximum_donation', 5000.0 ),
					'donation_type'              => get_post_meta( $post_id, 'donation_type', true ),
					'frequency_options'          => get_post_meta( $post_id, 'frequency_options', true ) ?: [
						'12 months' => __( 'Yearly', 'kudos-donations' ),
						'3 months'  => __( 'Quarterly', 'kudos-donations' ),
						'1 month'   => __( 'Monthly', 'kudos-donations' ),
					],
					'email_enabled'              => $this->get_meta_bool( $post_id, 'email_enabled', true ),
					'email_required'             => $this->get_meta_bool( $post_id, 'email_required', true ),
					'name_enabled'               => $this->get_meta_bool( $post_id, 'name_enabled', true ),
					'name_required'              => $this->get_meta_bool( $post_id, 'name_required', true ),
					'address_enabled'            => $this->get_meta_bool( $post_id, 'address_enabled', false ),
					'address_required'           => $this->get_meta_bool( $post_id, 'address_required', false ),
					'message_enabled'            => $this->get_meta_bool( $post_id, 'message_enabled', false ),
					'message_required'           => $this->get_meta_bool( $post_id, 'message_required', false ),
					'theme_color'                => get_post_meta( $post_id, 'theme_color', true ) ?: '#ff9f1c',
					'terms_link'                 => get_post_meta( $post_id, 'terms_link', true ),
					'privacy_link'               => get_post_meta( $post_id, 'privacy_link', true ),
					'show_return_message'        => $this->get_meta_bool( $post_id, 'show_return_message', false ),
					'use_custom_return_url'      => $this->get_meta_bool( $post_id, 'use_custom_return_url', false ),
					'custom_return_url'          => get_post_meta( $post_id, 'custom_return_url', true ),
					'payment_description_format' => get_post_meta( $post_id, 'payment_description_format', true ),
					'custom_styles'              => get_post_meta( $post_id, 'custom_styles', true ),
					'initial_title'              => get_post_meta( $post_id, 'initial_title', true ),
					'initial_description'        => get_post_meta( $post_id, 'initial_description', true ),
					'subscription_title'         => get_post_meta( $post_id, 'subscription_title', true ) ?: __( 'Subscription', 'kudos-donations' ),
					'subscription_description'   => get_post_meta( $post_id, 'subscription_description', true ) ?: __( 'How often would you like to donate?', 'kudos-donations' ),
					'address_title'              => get_post_meta( $post_id, 'address_title', true ) ?: __( 'Address', 'kudos-donations' ),
					'address_description'        => get_post_meta( $post_id, 'address_description', true ) ?: __( 'Please fill in your address', 'kudos-donations' ),
					'message_title'              => get_post_meta( $post_id, 'message_title', true ) ?: __( 'Message', 'kudos-donations' ),
					'message_description'        => get_post_meta( $post_id, 'message_description', true ) ?: __( 'Leave a message.', 'kudos-donations' ),
					'payment_title'              => get_post_meta( $post_id, 'payment_title', true ) ?: __( 'Payment', 'kudos-donations' ),
					'payment_description'        => get_post_meta( $post_id, 'payment_description', true ) ?: __( 'By clicking donate you agree to the following payment:', 'kudos-donations' ),
					'return_message_title'       => get_post_meta( $post_id, 'return_message_title', true ) ?: __( 'Payment received', 'kudos-donations' ),
					'return_message_text'        => get_post_meta( $post_id, 'return_message_text', true ) ?: __( 'Thank you for your donation!', 'kudos-donations' ),
					'created_at'                 => get_post_time( 'Y-m-d H:i:s', true, $post_id ),
					'updated_at'                 => get_post_modified_time( 'Y-m-d H:i:s', true, $post_id ),
				]
			);
			$result   = $campaign_repo->insert( $campaign );
			if ( false === $result ) {
				$this->logger->error( "Failed to insert campaign post $post_id", [ 'db_error' => $campaign_repo->get_last_db_error() ] );
			} else {
				$this->logger->info( "Migrated campaign post $post_id", [ 'id' => $result ] );
			}
		}

		return \count( $ids );
	}

	/**
	 * Migrates kudos_transaction CPTs to the kudos_transactions table in chunks.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_transactions( int $limit ): int {
		$post_type        = 'kudos_transaction';
		$transaction_repo = $this->get_repository( TransactionRepository::class );
		$campaign_repo    = $this->get_repository( CampaignRepository::class );
		$donor_repo       = $this->get_repository( DonorRepository::class );

		$ids = $this->get_unmigrated_posts( $transaction_repo, $post_type, $limit );

		if ( empty( $ids ) ) {
			$this->logger->info( 'No more transactions to migrate.' );
			return 0;
		}

		// Create a campaign map to migrate old id to new id.
		$campaign_map = [];
		/** @var CampaignEntity[] $campaign_rows */
		$campaign_rows = $campaign_repo->all();
		foreach ( $campaign_rows as $row ) {
			$campaign_map[ $row->wp_post_id ] = $row->id;
		}

		// Create a donor map to migrate old id to new id.
		$donor_map = [];
		/** @var DonorEntity[] $donor_rows */
		$donor_rows = $donor_repo->all();
		foreach ( $donor_rows as $row ) {
			$donor_map[ $row->wp_post_id ] = $row->id;
		}

		foreach ( $ids as $post_id ) {
			$campaign_id = (int) get_post_meta( $post_id, 'campaign_id', true );
			$donor_id    = (int) get_post_meta( $post_id, 'donor_id', true );

			$transaction = new TransactionEntity(
				[
					'wp_post_id'         => $post_id,
					'title'              => get_post_field( 'post_title', $post_id ),
					'value'              => $this->get_meta_float( $post_id, 'value' ) ?? 0,
					'currency'           => get_post_meta( $post_id, 'currency', true ) ?: 'EUR',
					'status'             => get_post_meta( $post_id, 'status', true ),
					'method'             => get_post_meta( $post_id, 'method', true ),
					'mode'               => get_post_meta( $post_id, 'mode', true ),
					'sequence_type'      => get_post_meta( $post_id, 'sequence_type', true ),
					'donor_id'           => $donor_map[ $donor_id ] ?? null,
					'campaign_id'        => $campaign_map[ $campaign_id ] ?? null,
					'vendor'             => 'mollie', // All payments are currently made with Mollie.
					'subscription_id'    => null, // Populated on second pass since subscriptions not available yet.
					'vendor_customer_id' => get_post_meta( $post_id, 'vendor_customer_id', true ),
					'vendor_payment_id'  => get_post_meta( $post_id, 'vendor_payment_id', true ),
					'invoice_number'     => (int) get_post_meta( $post_id, 'invoice_number', true ),
					'checkout_url'       => get_post_meta( $post_id, 'checkout_url', true ),
					'message'            => get_post_meta( $post_id, 'message', true ),
					'refunds'            => get_post_meta( $post_id, 'refunds', true ),
					'created_at'         => get_post_time( 'Y-m-d H:i:s', true, $post_id ),
					'updated_at'         => get_post_modified_time( 'Y-m-d H:i:s', true, $post_id ),
				]
			);

			$result = $transaction_repo->insert( $transaction );
			if ( false === $result ) {
				$this->logger->error( "Failed to insert transaction post $post_id", [ 'db_error' => $transaction_repo->get_last_db_error() ] );
			} else {
				$this->logger->info( "Migrated transaction post $post_id", [ 'id' => $result ] );
			}
		}

		return \count( $ids );
	}

	/**
	 * Migrates kudos_subscription CPTs to the kudos_subscriptions table in chunks.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_subscriptions( int $limit ): int {
		$post_type         = 'kudos_subscription';
		$transaction_repo  = $this->get_repository( TransactionRepository::class );
		$subscription_repo = $this->get_repository( SubscriptionRepository::class );
		$donor_repo        = $this->get_repository( DonorRepository::class );

		$ids = $this->get_unmigrated_posts( $subscription_repo, $post_type, $limit );

		if ( empty( $ids ) ) {
			$this->logger->info( 'No more subscriptions to migrate.' );
			return 0;
		}

		// Create a transaction map to migrate old id to new id.
		$transaction_map = [];
		/** @var TransactionEntity[] $transaction_rows */
		$transaction_rows = $transaction_repo->all();
		foreach ( $transaction_rows as $row ) {
			$transaction_post_id  = $row->wp_post_id;
			$legacy_donor_post_id = (int) get_post_meta( $transaction_post_id, 'donor_id', true );

			$transaction_map[ $transaction_post_id ] = [
				'id'       => $row->id,
				'donor_id' => $legacy_donor_post_id,
			];
		}

		// Create a donor map to migrate old id to new id.
		$donor_map = [];
		/** @var DonorEntity[] $donor_rows */
		$donor_rows = $donor_repo->all();
		foreach ( $donor_rows as $row ) {
			$donor_map[ $row->wp_post_id ] = $row->id;
		}

		foreach ( $ids as $post_id ) {
			$legacy_transaction_post_id = (int) get_post_meta( $post_id, 'transaction_id', true );
			$transaction_id             = null;
			$donor_id                   = null;

			if ( isset( $transaction_map[ $legacy_transaction_post_id ] ) ) {
				$transaction_entry = $transaction_map[ $legacy_transaction_post_id ];
				$transaction_id    = $transaction_entry['id'];

				$legacy_donor_post_id = $transaction_entry['donor_id'];
				$donor_id             = $donor_map[ $legacy_donor_post_id ] ?? null;
			}

			// Get campaign.
			$campaign_id = null;
			/** @var TransactionEntity $transaction */
			if ( $transaction_id ) {
				$transaction = $transaction_repo->get( $transaction_id );
				if ( $transaction ) {
					$campaign_id = $transaction->campaign_id;
				}
			}

			$subscription = new SubscriptionEntity(
				[
					'wp_post_id'             => $post_id,
					'title'                  => get_post_field( 'post_title', $post_id ),
					'value'                  => $this->get_meta_float( $post_id, 'value' ),
					'currency'               => get_post_meta( $post_id, 'currency', true ),
					'frequency'              => get_post_meta( $post_id, 'frequency', true ),
					'years'                  => (int) get_post_meta( $post_id, 'years', true ),
					'status'                 => get_post_meta( $post_id, 'status', true ),
					'donor_id'               => $donor_id,
					'campaign_id'            => $campaign_id,
					'vendor_customer_id'     => get_post_meta( $post_id, 'customer_id', true ),
					'vendor_subscription_id' => get_post_meta( $post_id, 'vendor_subscription_id', true ),
					'created_at'             => get_post_time( 'Y-m-d H:i:s', true, $post_id ),
					'updated_at'             => get_post_modified_time( 'Y-m-d H:i:s', true, $post_id ),
				]
			);

			$result = $subscription_repo->insert( $subscription );
			if ( false === $result ) {
				$this->logger->error( "Failed to insert subscription post $post_id", [ 'db_error' => $subscription_repo->get_last_db_error() ] );
			} else {
				$this->logger->info( "Migrated subscription post $post_id", [ 'data' => $subscription->to_array() ] );
			}
		}

		return \count( $ids );
	}

	/**
	 * Updates transactions with correct subscription_id.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function backfill_transactions_from_subscription( int $limit ): int {

		$transaction_repo  = $this->get_repository( TransactionRepository::class );
		$subscription_repo = $this->get_repository( SubscriptionRepository::class );

		// Get subscriptions that still have transactions needing backfill.
		/** @var SubscriptionEntity[] $subscriptions */
		$subscriptions = $subscription_repo->query(
			[
				'columns' => [ 'id', 'wp_post_id' ],
				'orderby' => 'id',
				'order'   => 'ASC',
				'limit'   => $limit,
			]
		);

		if ( empty( $subscriptions ) ) {
			$this->logger->info( 'No more transactions to backfill.' );
			return 0;
		}

		$processed = 0;

		foreach ( $subscriptions as $subscription ) {
			$subscription_post_id = $subscription->wp_post_id;
			$subscription_id      = $subscription->id;

			// Get legacy transaction post ID from subscription post meta.
			$transaction_post_id = get_post_meta(
				$subscription_post_id,
				'transaction_id',
				true
			);

			if ( empty( $transaction_post_id ) ) {
				$this->logger->warning( "No transaction_id meta found for subscription $subscription_post_id" );
				continue;
			}

			// Find transaction row by wp_post_id.
			/** @var ?TransactionEntity $transaction */
			$transaction = $transaction_repo->find_one_by( [ 'wp_post_id' => (int) $transaction_post_id ] );

			if ( null === $transaction ) {
				$this->logger->warning( "No migrated transaction found for post ID $transaction_post_id" );
				continue;
			}

			// Skip if already linked.
			if ( null !== $transaction->subscription_id ) {
				continue;
			}

			// Update transaction row with the resolved subscription_id.
			$transaction->subscription_id = $subscription_id;
			$transaction_repo->update( $transaction );
			++$processed;

			$this->logger->info( "Linked transaction $transaction_post_id to subscription $subscription_id" );
		}

		return $processed;
	}

	/**
	 * Updates transactions with correct subscription_id.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function backfill_remaining_transactions( int $limit ): int {
		$transaction_repo  = $this->get_repository( TransactionRepository::class );
		$subscription_repo = $this->get_repository( SubscriptionRepository::class );

		/** @var TransactionEntity[] $orphaned_transactions */
		$orphaned_transactions = $transaction_repo->query(
			[
				'where'   => [
					'sequence_type'   => 'recurring',
					'subscription_id' => null,
				],
				'orderby' => 'id',
				'order'   => 'ASC',
				'limit'   => $limit,
			]
		);

		if ( empty( $orphaned_transactions ) ) {
			$this->logger->info( 'No orphaned recurring transactions to backfill.' );
			return 0;
		}

		/** @var SubscriptionEntity[] $subscriptions */
		$subscriptions = $subscription_repo->all();

		$simple_map = []; // donor_id-value => [sub_ids...].
		$strict_map = []; // donor_id-value-campaign_id => [sub_ids...].

		foreach ( $subscriptions as $sub ) {
			$donor_id = $sub->donor_id ?? null;
			$value    = $sub->value ?? null;

			if ( ! $donor_id || ! $value ) {
				continue;
			}

			$key_simple = "$donor_id-$value";

			if ( ! isset( $simple_map[ $key_simple ] ) ) {
				$simple_map[ $key_simple ] = [];
			}
			$simple_map[ $key_simple ][] = $sub->id;

			// Try to resolve campaign_id for strict fallback.
			/** @var TransactionEntity $transaction */
			$transaction = $transaction_repo->find_one_by(
				[
					'subscription_id' => $sub->id,
				]
			);

			$campaign_id = $transaction->campaign_id ?? null;
			if ( $campaign_id ) {
				$key_strict = "$donor_id-$value-$campaign_id";

				if ( ! isset( $strict_map[ $key_strict ] ) ) {
					$strict_map[ $key_strict ] = [];
				}
				$strict_map[ $key_strict ][] = $sub->id;
			}
		}

		$processed = 0;

		// Now backfill transactions.
		foreach ( $orphaned_transactions as $transaction ) {
			$donor_id    = $transaction->donor_id;
			$value       = $transaction->value;
			$campaign_id = $transaction->campaign_id;

			$key_simple = "$donor_id-$value";
			$key_strict = "$donor_id-$value-$campaign_id";

			if ( isset( $simple_map[ $key_simple ] ) && \count( $simple_map[ $key_simple ] ) === 1 ) {
				$subscription_id = $simple_map[ $key_simple ][0];

				$transaction->subscription_id = $subscription_id;
				$transaction_repo->update( $transaction );
				++$processed;

				$this->logger->info( "Backfilled transaction $transaction->id with subscription $subscription_id via simple match" );
			} elseif ( isset( $strict_map[ $key_strict ] ) && \count( $strict_map[ $key_strict ] ) === 1 ) {
				$subscription_id = $strict_map[ $key_strict ][0];

				$transaction->subscription_id = $subscription_id;
				$transaction_repo->update( $transaction );
				++$processed;

				$this->logger->info( "Backfilled transaction $transaction->id with subscription $subscription_id via strict match" );
			} elseif ( ( isset( $simple_map[ $key_simple ] ) && \count( $simple_map[ $key_simple ] ) > 1 ) ||
						( isset( $strict_map[ $key_strict ] ) && \count( $strict_map[ $key_strict ] ) > 1 ) ) {
				$this->logger->warning(
					"Ambiguous match for transaction $transaction->id",
					[
						'donor_id'    => $donor_id,
						'value'       => $value,
						'campaign_id' => $campaign_id,
					]
				);
			} else {
				$this->logger->warning(
					"No matching subscription found for transaction $transaction->id",
					[
						'donor_id'    => $donor_id,
						'value'       => $value,
						'campaign_id' => $campaign_id,
					]
				);
			}
		}

		return $processed;
	}

	/**
	 * Links donors to transactions based on their vendor_customer_id.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function relink_donors_to_transactions( int $limit ): int {
		return $this->link_service->link_entities( TransactionRepository::class, 'donor_id', 'vendor_customer_id', DonorRepository::class, 'vendor_customer_id', $limit );
	}

	/**
	 * Links donors to subscriptions based on their vendor_customer_id.
	 *
	 * @param int $limit The number of records to fetch.
	 */
	public function relink_donors_to_subscriptions( int $limit ): int {
		return $this->link_service->link_entities( SubscriptionRepository::class, 'donor_id', 'vendor_customer_id', DonorRepository::class, 'vendor_customer_id', $limit );
	}

	/**
	 * Refresh mollie status to populate new option.
	 */
	public function refresh_mollie_status(): void {
		$this->mollie->refresh();
	}

	/**
	 * Returns a meta bool value with default.
	 *
	 * @param int    $post_id The post id.
	 * @param string $key The meta key.
	 * @param bool   $default_value The default value to return if meta value is empty.
	 */
	private function get_meta_bool( int $post_id, string $key, bool $default_value ): bool {
		$raw = get_post_meta( $post_id, $key, true );
		return '' === $raw ? $default_value : filter_var( $raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? $default_value;
	}

	/**
	 * Returns a meta float value with default.
	 *
	 * @param int    $post_id The post id.
	 * @param string $key The meta key.
	 * @param ?float $default_value The default value to return if meta value is empty.
	 */
	private function get_meta_float( int $post_id, string $key, ?float $default_value = null ): ?float {
		$value = get_post_meta( $post_id, $key, true );
		return is_numeric( $value ) ? (float) $value : $default_value;
	}

	/**
	 * @param BaseRepository $repository The repository to use for fetching already migrated posts.
	 * @param string         $post_type The post type to fetch unmigrated posts from.
	 * @param int            $limit The number of posts to fetch.
	 * @return int[]|WP_Post[]
	 */
	public function get_unmigrated_posts( BaseRepository $repository, string $post_type, int $limit ): array {
		$migrated     = $repository->query( [ 'columns' => [ 'wp_post_id' ] ] );
		$migrated_ids = array_map( fn( $row ) => $row->wp_post_id, $migrated );

		$args = [
			'post_type'        => $post_type,
			'post_status'      => 'any',
			'posts_per_page'   => $limit,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'fields'           => 'ids',
			'suppress_filters' => false,
		];

		if ( ! empty( $migrated_ids ) ) {
			$args['post__not_in'] = $migrated_ids;
		}

		return get_posts( $args );
	}
}
