<?php
/**
 * Abstract Entity class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Entity;

use DateTime;

abstract class AbstractEntity {

	/**
	 * The entities' database id.
	 *
	 * @var int
	 */
	public $id;
	/**
	 * The date first entered into database.
	 *
	 * @var DateTime
	 */
	public $created;
	/**
	 * The date the entity was last changed.
	 *
	 * @var DateTime
	 */
	public $last_updated;

	/**
	 * Entity object constructor.
	 *
	 * @param array|null $args Array of entities properties and values.
	 */
	public function __construct( ?array $args = null ) {

		if ( null !== $args ) {
			$this->set_fields( $args );
		}
	}

	/**
	 * Set class properties based on array values.
	 *
	 * @param array $fields Array of entities properties and values.
	 */
	public function set_fields( array $fields ) {

		foreach ( $fields as $property => $value ) {
			if ( property_exists( static::class, $property ) ) {
				$this->$property = $value;
			}
		}
	}

	/**
	 * Returns the table name associated with entity.
	 *
	 * @param bool $prefix Whether to return the prefix or not.
	 */
	public static function get_table_name( bool $prefix = true ): string {

		global $wpdb;

		return $prefix ? $wpdb->prefix . static::TABLE : static::TABLE;
	}

	/**
	 * Returns class as an array.
	 */
	public function to_array(): array {

		return get_object_vars( $this );
	}

	/**
	 * Returns the name of the entity (e.g. Transaction).
	 *
	 * @return string The name of the entities class without the word 'Entity'.
	 */
	public static function get_entity_name(): string {

		$array      = explode( '\\', static::class );
		$class      = array_pop( $array );
		$entity_pos = strpos( $class, 'Entity' );

		return substr( $class, 0, $entity_pos );
	}

	/**
	 * Returns the entities ID as a string.
	 */
	public function __toString(): string {

		return (string) $this->id;
	}
}
