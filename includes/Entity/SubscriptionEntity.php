<?php
/**
 * SubscriptionEntity class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Entity;

class SubscriptionEntity extends BaseEntity {

	public float $value;
	public string $currency;
	public string $frequency;
	public ?int $years;
	public string $status;
	public ?int $transaction_id;
	public ?int $donor_id;
	public ?int $campaign_id;
	public ?string $vendor_customer_id;
	public ?string $vendor_subscription_id;
	public ?DonorEntity $donor;
	public ?TransactionEntity $transaction;
	public ?CampaignEntity $campaign;

	/**
	 * {@inheritDoc}
	 */
	protected function defaults(): array {
		return [
			'currency' => 'EUR',
		];
	}
}
