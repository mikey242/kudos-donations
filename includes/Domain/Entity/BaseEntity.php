<?php
/**
 * BaseEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Domain\Entity;

class BaseEntity {

	public ?int $id      = null;
	public string $title = '';
	public ?int $wp_post_id;
	public ?string $created_at;
	public ?string $updated_at;

	/**
	 * Constructor for assigning object properties.
	 *
	 * @param array<string, mixed> $data The raw row data.
	 * @param bool                 $apply_defaults Whether to merge incoming data with specified defaults.
	 */
	public function __construct( array $data, bool $apply_defaults = true ) {
		$this->merge( $apply_defaults ? array_merge( $this->defaults(), $data ) : $data );
	}

	/**
	 * Specify the default values for this entity.
	 */
	protected function defaults(): array {
		return [];
	}

	/**
	 * Converts this entity into an array.
	 */
	public function to_array(): array {
		return get_object_vars( $this );
	}

	/**
	 * Merge provided data into entity properties.
	 *
	 * @param array<string, mixed> $data Raw data to use.
	 */
	public function merge( array $data ): void {
		foreach ( $data as $key => $value ) {
			if ( \property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}
	}
}
