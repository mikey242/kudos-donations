<?php
/**
 * Migration to create the campaigns table.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Lifecycle\SchemaInstaller;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;
use Psr\Log\LoggerInterface;

class Version500 extends BaseMigration {

	private CampaignRepository $campaign_repository;
	private TransactionRepository $transaction_repository;
	private DonorRepository $donor_repository;
	private SubscriptionRepository $subscription_repository;

	/**
	 * {@inheritDoc}
	 */
	public function __construct( WpDb $wpdb, LoggerInterface $logger ) {
		parent::__construct( $wpdb, $logger );
		( new SchemaInstaller( $wpdb ) )->on_plugin_activation();
		$this->campaign_repository     = new CampaignRepository( $wpdb );
		$this->transaction_repository  = new TransactionRepository( $wpdb );
		$this->donor_repository        = new DonorRepository( $wpdb );
		$this->subscription_repository = new SubscriptionRepository( $wpdb );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_migration_jobs(): array {
		return [
			'donors'        => $this->job( [ $this, 'migrate_donors' ], 'Migrating Kudos Donors to DB', true ),
			'campaigns'     => $this->job( [ $this, 'migrate_campaigns' ], 'Migrating Kudos Campaigns to DB', true ),
			'transactions'  => $this->job( [ $this, 'migrate_transactions' ], 'Migrating Kudos Transactions to DB', true ),
			'subscriptions' => $this->job( [ $this, 'migrate_subscriptions' ], 'Migrating Kudos Subscriptions to DB', true ),
		];
	}

	/**
	 * Migrates kudos_donor CPTs to the kudos_donors table in chunks.
	 *
	 * @param string $step The step name.
	 */
	public function migrate_donors( string $step ): bool {
		$offset    = $this->progress[ $step ]['offset'] ?? 0;
		$limit     = self::DEFAULT_CHUNK_SIZE;
		$post_type = 'kudos_donor';

		$posts = get_posts(
			[
				'post_type'        => $post_type,
				'post_status'      => 'any',
				'numberposts'      => $limit,
				'offset'           => $offset,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'suppress_filters' => false,
			]
		);

		if ( empty( $posts ) ) {
			$this->logger->info( 'No more donors to migrate.' );
			return true;
		}

		foreach ( $posts as $post ) {
			$post_id = $post->ID;

			$existing = $this->donor_repository->find_by_post_id( $post_id );
			if ( $existing ) {
				$this->logger->info( "Donor post {$post_id} already migrated. Skipping." );
				continue;
			}

			$data = [
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
				'created_at'         => get_post_time( 'Y-m-d H:i:s', true, $post ),
				'updated_at'         => get_post_modified_time( 'Y-m-d H:i:s', true, $post ),
			];

			$this->donor_repository->insert( $data );
			$this->logger->info( "Migrated donor post {$post_id}" );
		}

		$this->progress[ $step ]['offset'] = $offset + $limit;
		$this->update_progress();

		return false;
	}

	/**
	 * Migrates kudos_campaign CPTs to the kudos_campaigns table in chunks.
	 *
	 * @param string $step The step name.
	 */
	public function migrate_campaigns( string $step ): bool {
		$offset    = $this->progress[ $step ]['offset'] ?? 0;
		$limit     = self::DEFAULT_CHUNK_SIZE;
		$post_type = 'kudos_campaign';

		$posts = get_posts(
			[
				'post_type'        => $post_type,
				'post_status'      => 'any',
				'numberposts'      => $limit,
				'offset'           => $offset,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'suppress_filters' => false,
			]
		);

		if ( empty( $posts ) ) {
			$this->logger->info( 'No more campaigns to migrate.' );
			return true; // Migration complete.
		}

		foreach ( $posts as $post ) {
			$post_id = $post->ID;

			$existing = $this->campaign_repository->find_by_post_id( $post_id );
			if ( $existing ) {
				$this->logger->info( "Campaign post {$post_id} already migrated. Skipping." );
				continue;
			}

			$data = [
				'wp_post_id'                 => $post_id,
				'title'                      => get_post_field( 'post_title', $post_id ),
				'currency'                   => get_post_meta( $post_id, 'currency', true ),
				'goal'                       => get_post_meta( $post_id, 'goal', true ),
				'show_goal'                  => (bool) get_post_meta( $post_id, 'show_goal', true ),
				'additional_funds'           => get_post_meta( $post_id, 'additional_funds', true ),
				'amount_type'                => get_post_meta( $post_id, 'amount_type', true ),
				'fixed_amounts'              => wp_json_encode( get_post_meta( $post_id, 'fixed_amounts', true ) ),
				'minimum_donation'           => get_post_meta( $post_id, 'minimum_donation', true ),
				'maximum_donation'           => get_post_meta( $post_id, 'maximum_donation', true ),
				'donation_type'              => get_post_meta( $post_id, 'donation_type', true ),
				'frequency_options'          => wp_json_encode( get_post_meta( $post_id, 'frequency_options', true ) ),
				'email_enabled'              => (bool) get_post_meta( $post_id, 'email_enabled', true ),
				'email_required'             => (bool) get_post_meta( $post_id, 'email_required', true ),
				'name_enabled'               => (bool) get_post_meta( $post_id, 'name_enabled', true ),
				'name_required'              => (bool) get_post_meta( $post_id, 'name_required', true ),
				'address_enabled'            => (bool) get_post_meta( $post_id, 'address_enabled', true ),
				'address_required'           => (bool) get_post_meta( $post_id, 'address_required', true ),
				'message_enabled'            => (bool) get_post_meta( $post_id, 'message_enabled', true ),
				'message_required'           => (bool) get_post_meta( $post_id, 'message_required', true ),
				'theme_color'                => get_post_meta( $post_id, 'theme_color', true ),
				'terms_link'                 => get_post_meta( $post_id, 'terms_link', true ),
				'privacy_link'               => get_post_meta( $post_id, 'privacy_link', true ),
				'show_return_message'        => (bool) get_post_meta( $post_id, 'show_return_message', true ),
				'use_custom_return_url'      => (bool) get_post_meta( $post_id, 'use_custom_return_url', true ),
				'custom_return_url'          => get_post_meta( $post_id, 'custom_return_url', true ),
				'payment_description_format' => get_post_meta( $post_id, 'payment_description_format', true ),
				'custom_styles'              => get_post_meta( $post_id, 'custom_styles', true ),
				'initial_title'              => get_post_meta( $post_id, 'initial_title', true ),
				'initial_description'        => get_post_meta( $post_id, 'initial_description', true ),
				'subscription_title'         => get_post_meta( $post_id, 'subscription_title', true ),
				'subscription_description'   => get_post_meta( $post_id, 'subscription_description', true ),
				'address_title'              => get_post_meta( $post_id, 'address_title', true ),
				'address_description'        => get_post_meta( $post_id, 'address_description', true ),
				'message_title'              => get_post_meta( $post_id, 'message_title', true ),
				'message_description'        => get_post_meta( $post_id, 'message_description', true ),
				'payment_title'              => get_post_meta( $post_id, 'payment_title', true ),
				'payment_description'        => get_post_meta( $post_id, 'payment_description', true ),
				'return_message_title'       => get_post_meta( $post_id, 'return_message_title', true ),
				'return_message_text'        => get_post_meta( $post_id, 'return_message_text', true ),
				'created_at'                 => get_post_time( 'Y-m-d H:i:s', true, $post ),
				'updated_at'                 => get_post_modified_time( 'Y-m-d H:i:s', true, $post ),
			];

			$this->campaign_repository->insert( $data );
			$this->logger->info( "Migrated campaign post {$post_id}" );
		}

		// Update offset and save progress.
		$this->progress[ $step ]['offset'] = $offset + $limit;
		$this->update_progress();

		return false; // Tell BaseMigration to resume.
	}

	/**
	 * Migrates kudos_transaction CPTs to the kudos_transactions table in chunks.
	 *
	 * @param string $step The step.
	 */
	public function migrate_transactions( string $step ): bool {
		$offset    = $this->progress[ $step ]['offset'] ?? 0;
		$limit     = self::DEFAULT_CHUNK_SIZE;
		$post_type = 'kudos_transaction';

		$posts = get_posts(
			[
				'post_type'        => $post_type,
				'post_status'      => 'any',
				'numberposts'      => $limit,
				'offset'           => $offset,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'suppress_filters' => false,
			]
		);

		if ( empty( $posts ) ) {
			$this->logger->info( 'No more transactions to migrate.' );
			return true;
		}

		foreach ( $posts as $post ) {
			$post_id = $post->ID;

			$existing = $this->transaction_repository->find_by_post_id( $post_id );
			if ( $existing ) {
				$this->logger->info( "Transaction post {$post_id} already migrated. Skipping." );
				continue;
			}

			// Create a campaign map to migrate old id to new id.
			$campaign_map  = [];
			$campaign_rows = $this->campaign_repository->all();
			foreach ( $campaign_rows as $row ) {
				$campaign_map[ $row['wp_post_id'] ] = $row['id'];
			}

			// Create a donor map to migrate old id to new id.
			$donor_map  = [];
			$donor_rows = $this->donor_repository->all();
			foreach ( $donor_rows as $row ) {
				$donor_map[ $row['wp_post_id'] ] = $row['id'];
			}

			$campaign_id = (int) get_post_meta( $post_id, 'campaign_id', true );
			$donor_id    = (int) get_post_meta( $post_id, 'donor_id', true );

			$data = [
				'wp_post_id'             => $post_id,
				'title'                  => get_post_field( 'post_title', $post_id ),
				'value'                  => (float) get_post_meta( $post_id, 'value', true ),
				'currency'               => get_post_meta( $post_id, 'currency', true ),
				'status'                 => get_post_meta( $post_id, 'status', true ),
				'method'                 => get_post_meta( $post_id, 'method', true ),
				'mode'                   => get_post_meta( $post_id, 'mode', true ),
				'sequence_type'          => get_post_meta( $post_id, 'sequence_type', true ),
				'donor_id'               => $donor_map[ $donor_id ] ?? null,
				'campaign_id'            => $campaign_map[ $campaign_id ] ?? null,
				'vendor'                 => get_post_meta( $post_id, 'vendor', true ),
				'vendor_payment_id'      => get_post_meta( $post_id, 'vendor_payment_id', true ),
				'vendor_customer_id'     => get_post_meta( $post_id, 'vendor_customer_id', true ),
				'vendor_subscription_id' => get_post_meta( $post_id, 'vendor_subscription_id', true ),
				'invoice_number'         => (int) get_post_meta( $post_id, 'invoice_number', true ),
				'checkout_url'           => get_post_meta( $post_id, 'checkout_url', true ),
				'message'                => get_post_meta( $post_id, 'message', true ),
				'refunds'                => get_post_meta( $post_id, 'refunds', true ),
				'created_at'             => get_post_time( 'Y-m-d H:i:s', true, $post ),
				'updated_at'             => get_post_modified_time( 'Y-m-d H:i:s', true, $post ),
			];

			$this->transaction_repository->insert( $data );
			$this->logger->info( "Migrated transaction post {$post_id}" );
		}

		$this->progress[ $step ]['offset'] = $offset + $limit;
		$this->update_progress();

		return false;
	}

	/**
	 * Migrates kudos_subscription CPTs to the kudos_subscriptions table in chunks.
	 *
	 * @param string $step The step.
	 */
	public function migrate_subscriptions( string $step ): bool {
		$offset    = $this->progress[ $step ]['offset'] ?? 0;
		$limit     = self::DEFAULT_CHUNK_SIZE;
		$post_type = 'kudos_subscription';

		$posts = get_posts(
			[
				'post_type'        => $post_type,
				'post_status'      => 'any',
				'numberposts'      => $limit,
				'offset'           => $offset,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'suppress_filters' => false,
			]
		);

		if ( empty( $posts ) ) {
			$this->logger->info( 'No more subscriptions to migrate.' );
			$this->progress[ $step ]['done'] = true;
			$this->update_progress();
			return true;
		}

		// Create a transaction map to migrate old id to new id.
		$transaction_map  = [];
		$transaction_rows = $this->transaction_repository->all();
		foreach ( $transaction_rows as $row ) {
			$transaction_post_id  = $row['wp_post_id'];
			$legacy_donor_post_id = (int) get_post_meta( $transaction_post_id, 'donor_id', true );

			$transaction_map[ $transaction_post_id ] = [
				'id'       => $row['id'],
				'donor_id' => $legacy_donor_post_id,
			];
		}

		// Create a donor map to migrate old id to new id.
		$donor_map  = [];
		$donor_rows = $this->donor_repository->all();
		foreach ( $donor_rows as $row ) {
			$donor_map[ $row['wp_post_id'] ] = $row['id'];
		}

		foreach ( $posts as $post ) {
			$post_id = $post->ID;

			$existing = $this->subscription_repository->find_by_post_id( $post_id );
			if ( $existing ) {
				$this->logger->info( "Subscription post {$post_id} already migrated. Skipping." );
				continue;
			}

			$legacy_transaction_post_id = (int) get_post_meta( $post_id, 'transaction_id', true );
			$transaction_id             = null;
			$donor_id                   = null;

			if ( isset( $transaction_map[ $legacy_transaction_post_id ] ) ) {
				$transaction_entry = $transaction_map[ $legacy_transaction_post_id ];
				$transaction_id    = $transaction_entry['id'];

				$legacy_donor_post_id = $transaction_entry['donor_id'];
				$donor_id             = $donor_map[ $legacy_donor_post_id ] ?? null;
			}

			$data = [
				'wp_post_id'             => $post_id,
				'title'                  => get_post_field( 'post_title', $post_id ),
				'value'                  => (float) get_post_meta( $post_id, 'value', true ),
				'currency'               => get_post_meta( $post_id, 'currency', true ),
				'frequency'              => get_post_meta( $post_id, 'frequency', true ),
				'years'                  => (int) get_post_meta( $post_id, 'years', true ),
				'status'                 => get_post_meta( $post_id, 'status', true ),
				'transaction_id'         => $transaction_id,
				'donor_id'               => $donor_id,
				'vendor_customer_id'     => get_post_meta( $post_id, 'customer_id', true ),
				'vendor_subscription_id' => get_post_meta( $post_id, 'vendor_subscription_id', true ),
				'created_at'             => get_post_time( 'Y-m-d H:i:s', true, $post ),
				'updated_at'             => get_post_modified_time( 'Y-m-d H:i:s', true, $post ),
			];

			$this->subscription_repository->insert( $data );
			$this->logger->info( "Migrated subscription post {$post_id}" );
		}

		$this->progress[ $step ]['offset'] = $offset + \count( $posts );
		$this->update_progress();

		return false;
	}
}
