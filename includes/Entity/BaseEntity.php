<?php
/**
 * BaseEntity class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Entity;

class BaseEntity {

	public int $id;
	public string $title;
	public ?int $wp_post_id;
	public string $created_at;
	public string $updated_at;

	/**
	 * Constructor for assigning object keys to .
	 *
	 * @param array<string, mixed> $data The raw row data.
	 * @param bool                 $apply_defaults Whether to merge incoming data with specified defaults.
	 */
	public function __construct( array $data, bool $apply_defaults = true ) {
		$data = $apply_defaults ? array_merge( $this->defaults(), $data ) : $data;
		$this->fill( $data );
	}

	/**
	 * Specif the default values for this entity.
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
	 * Update properties based on provided data.
	 *
	 * @param array $data Raw data to use.
	 */
	public function fill( array $data ): void {
		foreach ( $data as $key => $value ) {
			if ( \property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}
	}
}
