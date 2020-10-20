<?php

namespace Kudos\Entity;

interface EntityInterface {

	public function set_fields(array $fields);

	public static function get_table_name();

	public function to_array();

	public function __toString();

}