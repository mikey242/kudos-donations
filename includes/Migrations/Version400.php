<?php
/**
 * Migration for version 4.0.0.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 *
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\InvoiceService;
use IseardMedia\Kudos\Vendor\EmailVendor\SMTPVendor;
use IseardMedia\Kudos\Vendor\PaymentVendor\MolliePaymentVendor;
use WP_Post;

class Version400 extends BaseMigration {

	protected string $version = '4.0.0';

	/**
	 * {@inheritDoc}
	 */
	public function get_jobs(): array {
		return [
			'migrate_settings'               => $this->job( [ $this, 'migrate_settings' ], 'Migrating settings' ),
			'migrate_campaigns_to_posts'     => $this->job( [ $this, 'migrate_campaigns_to_posts' ], 'Migrating campaigns' ),
			'migrate_donors_to_posts'        => $this->job( [ $this, 'migrate_donors_to_posts' ], 'Migrating donors' ),
			'migrate_transactions_to_posts'  => $this->job( [ $this, 'migrate_transactions_to_posts' ], 'Migrating transactions' ),
			'migrate_subscriptions_to_posts' => $this->job( [ $this, 'migrate_subscriptions_to_posts' ], 'Migrating subscriptions' ),
		];
	}

	/**
	 * Migrate all the settings.
	 */
	public function migrate_settings(): int {
		$this->migrate_vendor_settings();
		$this->migrate_smtp_settings();
		return 1;
	}

	/**
	 * Migrate the old vendor settings.
	 */
	public function migrate_vendor_settings(): int {
		$vendor_mollie = get_option( '_kudos_vendor_mollie' );
		$test_key      = $vendor_mollie['test_key'] ?? null;
		$live_key      = $vendor_mollie['live_key'] ?? null;
		$mode          = $vendor_mollie['mode'] ?? 'test';

		if ( $live_key ) {
			update_option( MolliePaymentVendor::SETTING_API_KEY_LIVE, $live_key );
		}

		if ( $test_key ) {
			update_option( MolliePaymentVendor::SETTING_API_KEY_TEST, $test_key );
		}

		update_option( MolliePaymentVendor::SETTING_API_MODE, $mode );

		return 1;
	}

	/**
	 * Migrate custom SMTP config.
	 */
	public function migrate_smtp_settings(): int {
		$host       = get_option( '_kudos_smtp_host' ) ?? null;
		$port       = get_option( '_kudos_smtp_port' ) ?? null;
		$encryption = get_option( '_kudos_smtp_encryption' ) ?? null;
		$autotls    = get_option( '_kudos_smtp_autotls' ) ?? null;
		$username   = get_option( '_kudos_smtp_username' ) ?? null;
		$password   = get_option( '_kudos_smtp_password' ) ?? null;
		$from_email = get_option( '_kudos_smtp_from' ) ? get_option( '_kudos_smtp_from' ) : $username;

		$new_settings = [];

		$new_settings['from_name'] = get_bloginfo( 'name' );

		if ( $host ) {
			$new_settings['host'] = $host;
		}
		if ( $port ) {
			$new_settings['port'] = $port;
		}
		if ( $encryption ) {
			$new_settings['encryption'] = $encryption;
		}
		if ( $autotls ) {
			$new_settings['autotls'] = $autotls;
		}
		if ( $from_email ) {
			$new_settings['from_email'] = $from_email;
		}
		if ( $username ) {
			$new_settings['username'] = $username;
		}
		if ( $password ) {
			update_option( SMTPVendor::SETTING_SMTP_PASSWORD, $password );
		}

		update_option( SMTPVendor::SETTING_CUSTOM_SMTP, $new_settings );

		return 1;
	}

	/**
	 * Migrate campaigns from a settings array to CampaignPostTypes.
	 */
	public function migrate_campaigns_to_posts(): int {
		$campaigns = get_option( '_kudos_campaigns', [] );

		// Global settings.
		$theme_colour         = get_option( '_kudos_theme_colors' );
		$return_message_title = get_option( '_kudos_return_message_title' );
		$return_message_text  = get_option( '_kudos_return_message_text' );
		$custom_return_url    = get_option( '_kudos_custom_return_url' );
		$show_return_message  = get_option( '_kudos_completed_payment' ) === 'message';
		$custom_return_enable = get_option( '_kudos_completed_payment' ) === 'url';
		$terms_url            = get_option( '_kudos_terms_link' );
		$privacy_url          = get_option( '_kudos_privacy_link' );

		$total = 0;

		foreach ( $campaigns as $campaign ) {
			if ( \is_array( $campaign ) ) {
				$post_args = [
					'post_type'  => 'kudos_campaign',
					'post_title' => $campaign['name'] ?? 'Default',
					'post_name'  => $campaign['id'],
				];

				$meta_args = [
					'initial_title'         => $campaign['modal_title'] ?? '',
					'initial_description'   => $campaign['welcome_text'] ?? '',
					'address_enabled'       => $campaign['address_enabled'] ?? false,
					'address_required'      => $campaign['address_required'] ?? false,
					'message_enabled'       => $campaign['message_enabled'] ?? false,
					'amount_type'           => $campaign['amount_type'] ?? 'open',
					'goal'                  => $campaign['campaign_goal'] ?? '',
					'additional_funds'      => $campaign['additional_funds'] ?? '',
					'show_goal'             => $campaign['show_progress'] ?? false,
					'donation_type'         => $campaign['donation_type'] ?? 'oneoff',
					'fixed_amounts'         => explode( ',', $campaign['fixed_amounts'] ?? '' ) ?? [ 5, 10, 25, 50 ],
					'theme_color'           => $theme_colour ? $theme_colour['primary'] : '#ff9f1c',
					// Add these global settings which are now campaign scoped.
					'show_return_message'   => $show_return_message,
					'return_message_title'  => $return_message_title,
					'return_message_text'   => $return_message_text,
					'use_custom_return_url' => $custom_return_enable,
					'custom_return_url'     => $custom_return_url,
					'terms_link'            => $terms_url,
					'privacy_link'          => $privacy_url,
				];

				$new_campaign = self::save( $post_args, $meta_args );

				// Skip if post not created.
				if ( ! $new_campaign ) {
					continue;
				}

				// Store old and new ID for later reference.
				$mapping = get_transient( 'kudos_campaign_id_map' ) ?? [];
				$mapping = \is_array( $mapping ) ? $mapping : [];

				if ( ! empty( $campaign['id'] ) ) {
					$mapping[ $campaign['id'] ] = $new_campaign->ID;
				}
				set_transient( 'kudos_campaign_id_map', $mapping, DAY_IN_SECONDS );
				++$total;
			}
		}

		return 1;
	}

	/**
	 * Migrates donors from custom table to custom post type.
	 *
	 * @param int $offset Offset of results.
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_donors_to_posts( int $offset, int $limit ): int {
		$table_name = $this->wpdb->prefix . 'kudos_donors';

		// Check table exists.
		if ( ! $this->table_exists( $table_name ) ) {
			return 0;
		}

		// Get data in chunks.
		$rows = $this->get_rows( $table_name, $offset, $limit );

		if ( empty( $rows ) ) {
			return 0;
		}

		foreach ( $rows as $donor ) {
			$post_args = [
				'post_type' => 'kudos_donor',
				'post_date' => $donor->created ?? null,
			];

			$meta_args = [
				'email'              => $donor->email,
				'name'               => $donor->name,
				'business_name'      => $donor->business_name,
				'street'             => $donor->street,
				'postcode'           => $donor->postcode,
				'city'               => $donor->city,
				'country'            => $donor->country,
				'mode'               => $donor->mode,
				'vendor_customer_id' => $donor->customer_id ?? null,
			];

			$new_donor = self::save( $post_args, $meta_args );

			// Skip if donor not created.
			if ( ! $new_donor ) {
				continue;
			}

			$this->create_post_title( $new_donor->ID );

			// Update transient cache.
			if ( $donor->customer_id ?? null ) {
				$mapping = get_transient( 'kudos_donor_id_map' ) ?? [];
				$mapping = \is_array( $mapping ) ? $mapping : [];
				if ( ! empty( $donor->customer_id ) ) {
					$mapping[ $donor->customer_id ] = $new_donor->ID;
				}
				set_transient( 'kudos_donor_id_map', $mapping, DAY_IN_SECONDS );
			}
		}

		// Update progress.
		return \count( $rows );
	}

	/**
	 * Migrate transactions from kudos_transactions table to
	 * TransactionPostTypes.
	 *
	 * @param int $offset Offset of results.
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_transactions_to_posts( int $offset, int $limit ): int {
		$table_name = $this->wpdb->prefix . 'kudos_transactions';

		// Check table exists.
		if ( ! $this->table_exists( $table_name ) ) {
			return 0;
		}

		// Get cache.
		$donor_cache    = get_transient( 'kudos_donor_id_map' );
		$campaign_cache = get_transient( 'kudos_campaign_id_map' );

		// Get data.
		$invoice_number = (int) get_option( InvoiceService::SETTING_INVOICE_NUMBER, 1 );
		$rows           = $this->get_rows( $table_name, $offset, $limit );
		$mapping        = get_transient( 'kudos_transaction_id_map' ) ?? [];
		$mapping        = \is_array( $mapping ) ? $mapping : [];

		foreach ( $rows as $transaction ) {
			$post_args = [
				'post_type' => 'kudos_transaction',
				'post_date' => $transaction->created,
			];

			$meta_args = [
				'value'             => (int) $transaction->value,
				'currency'          => $transaction->currency,
				'status'            => $transaction->status,
				'method'            => $transaction->method,
				'mode'              => $transaction->mode,
				'sequence_type'     => $transaction->sequence_type,
				'donor_id'          => $donor_cache[ $transaction->customer_id ] ?? null,
				'vendor_payment_id' => $transaction->transaction_id,
				'refunds'           => $transaction->refunds,
				'campaign_id'       => $campaign_cache[ $transaction->campaign_id ] ?? null,
				'message'           => $transaction->message,
			];

			$new_transaction = self::save( $post_args, $meta_args );

			// Skip if post not created.
			if ( ! $new_transaction ) {
				continue;
			}

			$this->create_post_title( $new_transaction->ID );

			// If transaction is paid then add invoice number and iterate.
			if ( PaymentStatus::PAID === $transaction->status ) {
				self::save(
					[
						'ID'        => $new_transaction->ID,
						'post_type' => 'kudos_transaction',
					],
					[ 'invoice_number' => $invoice_number++ ]
				);
			}

			// Store old and new ID for later reference.
			if ( ! empty( $transaction->transaction_id ) ) {
				$mapping[ $transaction->transaction_id ] = $new_transaction->ID;
			}
		}

		set_transient( 'kudos_transaction_id_map', $mapping, DAY_IN_SECONDS );
		update_option( InvoiceService::SETTING_INVOICE_NUMBER, $invoice_number );

		// Update progress.
		return \count( $rows );
	}

	/**
	 * Migrate transactions from kudos_transactions table to
	 * TransactionPostTypes.
	 *
	 * @param int $offset Offset of results.
	 * @param int $limit The number of records to fetch.
	 */
	public function migrate_subscriptions_to_posts( int $offset, int $limit ): int {
		$table_name = $this->wpdb->prefix . 'kudos_subscriptions';

		// Check table exists.
		if ( ! $this->table_exists( $table_name ) ) {
			return 0;
		}

		// Cache.
		$transaction_cache = get_transient( 'kudos_transaction_id_map' ) ?? [];

		// Fetch data.
		$rows = $this->get_rows( $table_name, $offset, $limit );

		if ( empty( $rows ) ) {
			return 0;
		}

		foreach ( $rows as $subscription ) {
			$post_args = [
				'post_type' => 'kudos_subscription',
				'post_date' => $subscription->created,
			];

			$meta_args = [
				'value'                  => (int) $subscription->value,
				'currency'               => (string) $subscription->currency,
				'frequency'              => (string) $subscription->frequency,
				'years'                  => (int) $subscription->years,
				'customer_id'            => (string) $subscription->customer_id,
				'transaction_id'         => (string) $transaction_cache[ $subscription->transaction_id ] ?? '',
				'vendor_subscription_id' => (string) $subscription->subscription_id,
				'status'                 => (string) $subscription->status,
			];

			$new_subscription = self::save( $post_args, $meta_args );

			// Skip if post not created.
			if ( ! $new_subscription ) {
				continue;
			}

			$this->create_post_title( $new_subscription->ID );
		}

		return \count( $rows );
	}

	/**
	 * Fetch records.
	 *
	 * @param string $table_name Table name to query.
	 * @param int    $offset Offset of results.
	 * @param int    $limit The number of records to fetch.
	 * @return array|object|null
	 */
	private function get_rows( string $table_name, int $offset, int $limit = self::DEFAULT_CHUNK_SIZE ) {
		$query = $this->wpdb->prepare( "SELECT * FROM $table_name LIMIT %d OFFSET %d", $limit, $offset );
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Check if specified table exists.
	 *
	 * @param string $table_name The table name to check (e.g. wp_kudos_transactions).
	 */
	private function table_exists( string $table_name ): bool {
		// Check table exists.
		$check = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		if ( $this->wpdb->get_var( $check ) !== $table_name ) {
			$this->logger->error( 'Table not found for migration step', [ 'table' => $table_name ] );
			return false;
		}
		return true;
	}

	/**
	 * Update post title and content.
	 *
	 * @param int $post_id The id of the post.
	 */
	public function create_post_title( int $post_id ) {
		$post        = get_post( $post_id );
		$object_type = get_post_type_object( get_post_type( $post_id ) );
		$postarr     = [
			'ID' => $post_id,
		];

		switch ( $object_type->name ) {
			case 'kudos_transaction':
				$campaign_id        = $post->campaign_id ?? '';
				$donor_id           = $post->donor_id ?? '';
				$donor              = get_post( $donor_id );
				$campaign           = get_post( $campaign_id );
				$description_format = $campaign->payment_description_format ?? __( 'Donation ({{campaign_name}}) - {{order_id}}', 'kudos-donations' );

				$vars                 = [];
				$vars['{{order_id}}'] = Utils::get_formatted_id( $post->ID );
				$vars['{{type}}']     = $post->sequence_type ?? '';

				// Add donor variables if available.
				if ( $donor ) {
					$vars['{{donor_name}}']  = $donor->name ?? '';
					$vars['{{donor_email}}'] = $donor->email ?? '';
				}

				// Add campaign variables if available.
				if ( $campaign ) {
					$vars['{{campaign_name}}'] = $campaign->post_title;
				} else {
					$vars['({{campaign_name}})'] = '';
				}

				// Post content ready.
				$postarr['post_content'] = implode( ', ', $vars );

				// Generate title.
				$postarr['post_title'] = apply_filters(
					'kudos_payment_description',
					strtr( $description_format, $vars ),
					$post->sequence_type ?? 'oneoff',
					$post->ID,
					$campaign,
				);
				break;
			default:
				$single_name           = $object_type->labels->singular_name;
				$postarr['post_title'] = $single_name . \sprintf( ' (%1$s)', Utils::get_formatted_id( $post_id ) );
		}

		wp_update_post( $postarr );
	}

	/**
	 * Create or update a post.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters.
	 *
	 * @param array $post_args Array of post fields.
	 * @param array $meta_args Array of meta fields.
	 */
	public static function save( array $post_args, array $meta_args ): ?WP_Post {

		$post_id = isset( $post_args['ID'] ) ? absint( $post_args['ID'] ) : 0;

		$post_args = array_merge(
			[
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => '',
			],
			$post_args
		);

		// Save or update post.
		if ( $post_id ) {
			$post_id = wp_update_post( $post_args, true );
		} else {
			$post_id = wp_insert_post( $post_args, true );
		}

		// Bail if post not saved/updated.
		if ( is_wp_error( $post_id ) ) {
			return null;
		}

		// Update meta.
		foreach ( $meta_args as $meta_key => $meta_value ) {
			// Sanitize and save meta value.
			update_post_meta( $post_id, sanitize_key( $meta_key ), $meta_value );
		}

		// Return post object or null.
		return get_post( $post_id );
	}
}
