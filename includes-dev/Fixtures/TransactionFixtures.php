<?php
/**
 * Used to manage and create transaction fixtures.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use IseardMedia\Kudos\Repository\BaseRepository;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SequenceType;

class TransactionFixtures extends BaseFixtures {

	private const SUBSCRIPTION_POOL_KEY = '_kudos_fixture_subscription_pool';
	private SubscriptionRepository $subscription_repository;
	private DonorRepository $donor_repository;

	/**
	 * {@inheritDoc}
	 */
	protected function before(): void {
		delete_transient( self::SUBSCRIPTION_POOL_KEY );
		$this->repository              = new TransactionRepository( $this->wpdb );
		$this->subscription_repository = new SubscriptionRepository( $this->wpdb );
		$this->donor_repository        = new DonorRepository( $this->wpdb );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function generate_random_entity(): array {
		$wpdb     = $this->wpdb;
		$value    = $this->faker->numberBetween( 10, 200 );
		$currency = 'EUR';

		// Create first transactions.
		$subscription    = $this->reserve_subscription();
		$subscription_id = null;
		if ( $subscription ) {
			$subscription_id = $subscription[ BaseRepository::ID ];
			$sequence_type   = SequenceType::FIRST;
			$value           = $subscription[ SubscriptionRepository::VALUE ];
			$currency        = $subscription[ SubscriptionRepository::CURRENCY ];
		} else {
			$sequence_type = $this->pick_weighted(
				[
					SequenceType::ONEOFF    => 50,
					SequenceType::RECURRING => 40,
				]
			);
		}

		$campaign_id = null;
		$donor_id    = null;

		switch ( $sequence_type ) {
			case SequenceType::ONEOFF:
			case SequenceType::FIRST:
				$campaigns   = ( new CampaignRepository( $wpdb ) )->all();
				$donors      = ( new DonorRepository( $wpdb ) )->all();
				$campaign    = $campaigns[ array_rand( $campaigns ) ];
				$campaign_id = $campaign[ BaseRepository::ID ];
				$donor       = $donors ? $donors[ array_rand( $donors ) ] : null;
				$donor_id    = $donor[ BaseRepository::ID ] ?? null;
				if ( SequenceType::ONEOFF === $sequence_type ) {
					$value    = $this->faker->numberBetween( 10, 200 );
					$currency = $campaign[ CampaignRepository::CURRENCY ];
				}
				break;
			case SequenceType::RECURRING:
				$subscriptions     = $this->subscription_repository->all();
				$subscription      = $subscriptions[ array_rand( $subscriptions ) ];
				$subscription_id   = $subscription[ BaseRepository::ID ];
				$value             = $subscription[ SubscriptionRepository::VALUE ];
				$first_transaction = $this->repository->find_one_by(
					[
						TransactionRepository::SUBSCRIPTION_ID => $subscription_id,
					]
				);
				if ( $first_transaction ) {
					$campaign_id = $first_transaction[ TransactionRepository::CAMPAIGN_ID ];
					$donor_id    = $first_transaction[ TransactionRepository::DONOR_ID ];
					$currency    = $first_transaction[ TransactionRepository::CURRENCY ];
				}
				break;
		}

		return [
			TransactionRepository::CAMPAIGN_ID       => $campaign_id,
			TransactionRepository::DONOR_ID          => $donor_id,
			TransactionRepository::SEQUENCE_TYPE     => $sequence_type,
			TransactionRepository::VALUE             => $value,
			TransactionRepository::CURRENCY          => $currency,
			TransactionRepository::VENDOR_PAYMENT_ID => 'tr_' . wp_rand( 1000000, 9999999 ),
			TransactionRepository::MODE              => 'live',
			TransactionRepository::STATUS            => 'paid',
			TransactionRepository::SUBSCRIPTION_ID   => $subscription_id,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function after( array $created_entities ): void {
		foreach ( $created_entities as $transaction_id ) {
			$transaction = $this->repository->find( $transaction_id );
			if ( SequenceType::FIRST === $transaction[ TransactionRepository::SEQUENCE_TYPE ] ) {
				$donor_id = $transaction[ TransactionRepository::DONOR_ID ];
				$donor    = $this->donor_repository->find( $donor_id );
				$sub_id   = $transaction[ TransactionRepository::SUBSCRIPTION_ID ];
				$this->subscription_repository->save(
					[
						BaseRepository::ID               => $sub_id,
						SubscriptionRepository::TRANSACTION_ID => $transaction[ BaseRepository::ID ],
						SubscriptionRepository::DONOR_ID => $donor[ BaseRepository::ID ],
					]
				);
			}
		}
	}

	/**
	 * Returns valid subscription.
	 */
	private function reserve_subscription(): ?array {
		$pool = $this->get_subscription_pool();

		if ( empty( $pool ) ) {
			return null;
		}

		// Pick one at random.
		$keys        = array_keys( $pool );
		$selected_id = $keys[ array_rand( $keys ) ];
		$selected    = $pool[ $selected_id ];

		// Remove from pool and update transient.
		unset( $pool[ $selected_id ] );
		set_transient( self::SUBSCRIPTION_POOL_KEY, $pool, 3600 );

		return $selected;
	}

	/**
	 * Returns an array of valid fist transactions.
	 */
	private function get_subscription_pool(): array {
		$subscriptions = get_transient( self::SUBSCRIPTION_POOL_KEY );

		// Only rebuild if transient is NOT set (false).
		if ( false === $subscriptions ) {
			$subscription_repo = $this->subscription_repository;
			$subscriptions     = $subscription_repo->all();

			set_transient( self::SUBSCRIPTION_POOL_KEY, $subscriptions, 3600 );
		}

		return $subscriptions;
	}
}
