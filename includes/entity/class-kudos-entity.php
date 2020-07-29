<?php

namespace Kudos;

use DateTime;

class Entity {

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
	public function __construct($atts=null) {
		if(null !== $atts) {
			$this->set_fields($atts);
		}
	}

	/**
	 * Set class properties based on array values
	 *
	 * @param $atts
	 * @since   2.0.0
	 */
	public function set_fields($atts) {
		foreach ($atts as $property => $value) {
			if(property_exists($this, $property)) {
				$this->$property = $value;
			}
		}
	}

	/**
	 * Returns the table name associated with entity
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public static function getTableName() {
		global $wpdb;
		return $wpdb->prefix . static::TABLE;
	}
}