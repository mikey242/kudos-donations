<?php

namespace Kudos\Entity;

interface EntityInterface {

	/**
	 * Returns the name of the table
	 *
	 * @param bool $prefix Whether to prepend the prefix or not.
	 *
	 * @return string
	 */
	public static function get_table_name( bool $prefix = true );

	/**
	 * Set the entity properties.
	 *
	 * @param array $fields Array of entity properties and values.
	 *
	 * @return mixed
	 */
	public function set_fields( array $fields );

	/**
	 * Converts entity object to an array
	 *
	 * @return array
	 */
	public function to_array();

	/**
	 * Returns the entity as a string
	 *
	 * @return string
	 */
	public function __toString();

}
