<?php

namespace Kudos\Entity;

use DateTime;

abstract class AbstractEntity implements EntityInterface {

	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var DateTime
	 */
	public $created;
	/**
	 * @var DateTime
	 */
	public $last_updated;

	/**
	 * Entity object constructor.
	 *
	 * @param null|array $atts
	 *
	 * @since   2.0.0
	 */
	public function __construct( $atts = null ) {

		if ( null !== $atts ) {
			$this->set_fields( $atts );
		}

	}

	/**
	 * Set class properties based on array values
	 *
	 * @param array $atts
	 *
	 * @since   2.0.0
	 */
	public function set_fields( array $atts ) {

		foreach ( $atts as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->$property = $value;
			}
		}

	}

	/**
	 * Returns the table name associated with Entity
	 *
	 * @param bool $prefix Whether to return the prefix or not
	 * @return string
	 * @since   2.0.0
	 */
	public static function get_table_name($prefix = true) {

		global $wpdb;

		return $prefix ? $wpdb->prefix . static::TABLE : static::TABLE;

	}

	/**
	 * Returns class as an array using type casting
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function to_array() {

		return (array) $this;

	}

	/**
	 * @return string
	 * @since   2.0.0
	 */
	public function __toString() {

		return (string) $this->id;

	}
}