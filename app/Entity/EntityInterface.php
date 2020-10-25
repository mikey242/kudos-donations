<?php

namespace Kudos\Entity;

interface EntityInterface {

	public function set_fields(array $fields);

	/**
	 * @param bool $prefix
	 * @return string
	 */
	public static function get_table_name( bool $prefix = true );

	/**
	 * @return array
	 */
	public function to_array();

	/**
	 * @return string
	 */
	public function __toString();

}