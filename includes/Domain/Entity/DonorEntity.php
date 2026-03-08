<?php
/**
 * DonorEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Entity;

class DonorEntity extends BaseEntity {

	public ?string $mode;
	public string $email;
	public ?string $business_name;
	public ?string $name;
	public ?string $street;
	public ?string $postcode;
	public ?string $city;
	public ?string $country;
	public ?string $locale;
	public ?string $vendor_customer_id;
	public ?int $transaction_count;

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return trim( $this->name . ' <' . $this->email . '>' );
	}
}
