<?php
/**
 * TransactionEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Entity;

use IseardMedia\Kudos\Enum\PaymentStatus;

class TransactionEntity extends BaseEntity {

	public float $value;
	public string $currency;
	public string $status = PaymentStatus::OPEN;
	public ?string $method;
	public ?string $mode;
	public ?string $sequence_type;
	public ?int $donor_id;
	public ?int $campaign_id;
	public ?int $subscription_id;
	public ?string $refunds;
	public ?string $message;
	public ?string $vendor;
	public ?int $invoice_number;
	public ?string $checkout_url;
	public ?string $vendor_payment_id;
	public ?string $vendor_customer_id;
	public ?string $invoice_url;
	public ?CampaignEntity $campaign;
	public ?DonorEntity $donor;
	public ?SubscriptionEntity $subscription;
}
