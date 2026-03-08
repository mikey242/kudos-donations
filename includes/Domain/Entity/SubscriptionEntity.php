<?php
/**
 * SubscriptionEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Entity;

class SubscriptionEntity extends BaseEntity {

	public ?int $transaction_id;
	public ?TransactionEntity $transaction;
	public string $status;
	public string $currency;
	public float $value;
	public string $frequency;
	public ?int $years;
	public ?int $donor_id;
	public ?DonorEntity $donor;
	public ?int $campaign_id;
	public ?CampaignEntity $campaign;
	public ?string $vendor_customer_id;
	public ?string $vendor_subscription_id;
	public ?string $token;

	/**
	 * {@inheritDoc}
	 */
	protected function defaults(): array {
		return [
			'currency' => 'EUR',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return $this->vendor_subscription_id;
	}
}
