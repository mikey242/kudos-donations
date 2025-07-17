<?php
/**
 * DonorEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Entity;

class DonorEntity extends BaseEntity {

	public string $email;
	public ?string $mode               = null;
	public ?string $name               = null;
	public ?string $business_name      = null;
	public ?string $street             = null;
	public ?string $postcode           = null;
	public ?string $city               = null;
	public ?string $country            = null;
	public ?string $locale             = null;
	public ?string $vendor_customer_id = null;
	public ?float $total               = null;
}
