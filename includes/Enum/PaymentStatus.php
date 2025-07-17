<?php
/**
 * Payment Status Types.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Enum;

class PaymentStatus {

	public const PAID     = 'paid';
	public const OPEN     = 'open';
	public const CANCELED = 'canceled';
	public const EXPIRED  = 'expired';
	public const FAILED   = 'failed';
}
