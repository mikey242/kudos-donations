<?php
/**
 * TransactionEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Entity;

use IseardMedia\Kudos\Enum\PaymentStatus;

class TransactionEntity extends BaseEntity {

	public ?string $mode;
	public string $status = PaymentStatus::OPEN;
	public ?string $method;
	public ?string $sequence_type;
	public string $currency;
	public float $value;
	public ?int $donor_id;
	public ?DonorEntity $donor;
	public ?int $campaign_id;
	public ?CampaignEntity $campaign;
	public ?int $subscription_id;
	public ?SubscriptionEntity $subscription;
	public ?int $invoice_number;
	public ?string $refunds;
	public ?string $message;
	public ?string $vendor;
	public ?string $vendor_payment_id;
	public ?string $vendor_customer_id;
	public ?string $checkout_url;
	public ?string $receipt_url;
}
