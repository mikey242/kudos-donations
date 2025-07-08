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

use IseardMedia\Kudos\Entity\CampaignEntity;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SequenceType;

class TransactionFixtures extends BaseFixtures {

	private const SUBSCRIPTION_POOL_KEY = '_kudos_fixture_subscription_pool';
	private SubscriptionRepository $subscription_repository;

	/**
	 * {@inheritDoc}
	 */
	protected function before(): void {
		delete_transient( self::SUBSCRIPTION_POOL_KEY );
		$this->repository              = new TransactionRepository( $this->wpdb );
		$this->subscription_repository = new SubscriptionRepository( $this->wpdb );
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
			$subscription_id = $subscription->id;
			$sequence_type   = SequenceType::FIRST;
			$value           = $subscription->value;
			$currency        = $subscription->currency;
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
				/** @var CampaignEntity[] $campaigns */
				$campaigns = ( new CampaignRepository( $wpdb ) )->all();
				/** @var DonorEntity[] $donors */
				$donors      = ( new DonorRepository( $wpdb ) )->all();
				$campaign    = $campaigns[ array_rand( $campaigns ) ];
				$campaign_id = $campaign->id;
				$donor       = $donors ? $donors[ array_rand( $donors ) ] : null;
				$donor_id    = $donor->id ?? null;
				if ( SequenceType::ONEOFF === $sequence_type ) {
					$value    = $this->faker->numberBetween( 10, 200 );
					$currency = $campaign->currency;
				}
				break;
			case SequenceType::RECURRING:
				/** @var SubscriptionEntity[] $subscriptions */
				$subscriptions   = $this->subscription_repository->all();
				$subscription    = $subscriptions[ array_rand( $subscriptions ) ];
				$subscription_id = $subscription->id;
				$value           = $subscription->value;
				/** @var TransactionEntity $first_transaction */
				$first_transaction = $this->repository->find_one_by(
					[
						'subscription_id' => $subscription_id,
					]
				);
				if ( $first_transaction ) {
					$campaign_id = $first_transaction->campaign_id;
					$donor_id    = $first_transaction->donor_id;
					$currency    = $first_transaction->currency;
				}
				break;
		}

		return [
			'campaign_id'       => $campaign_id,
			'donor_id'          => $donor_id,
			'sequence_type'     => $sequence_type,
			'value'             => $value,
			'currency'          => $currency,
			'vendor_payment_id' => 'tr_' . wp_rand( 1000000, 9999999 ),
			'mode'              => 'live',
			'status'            => 'paid',
			'subscription_id'   => $subscription_id,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function after( array $created_entities ): void {
		foreach ( $created_entities as $transaction_id ) {
			/** @var TransactionEntity $transaction */
			$transaction = $this->repository->get( $transaction_id );
			if ( SequenceType::FIRST === $transaction->sequence_type ) {
				$donor_id = $transaction->donor_id;
				$sub_id   = $transaction->subscription_id;
				/** @var SubscriptionEntity $subscription */
				$subscription                 = $this->subscription_repository->get( $sub_id );
				$subscription->transaction_id = $transaction_id;
				$subscription->donor_id       = $donor_id;
				$this->subscription_repository->upsert( $subscription );
			}
		}
	}

	/**
	 * Returns valid subscription.
	 */
	private function reserve_subscription(): ?SubscriptionEntity {
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
	 *
	 * @return SubscriptionEntity[]
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
