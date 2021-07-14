<?php

namespace Kudos\Entity;

use DateTime;
use Exception;
use Kudos\Helpers\Utils;

abstract class AbstractEntity {

	/**
	 * The entities database id
	 *
	 * @var int
	 */
	public $id;
	/**
	 * The date first entered into database
	 *
	 * @var DateTime
	 */
	public $created;
	/**
	 * The date the entity was last changed
	 *
	 * @var DateTime
	 */
	public $last_updated;
	/**
	 * The entities secret
	 *
	 * @var string
	 */
	protected $secret;

	/**
	 * Entity object constructor.
	 *
	 * @param array|null $atts Array of entities properties and values.
	 */
	public function __construct( array $atts = null ) {

		if ( null !== $atts ) {
			$this->set_fields( $atts );
		}

	}

	/**
	 * Set class properties based on array values
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
	 * Create the hooks associated with the child entity.
	 */
	public static function create_hooks() {

		add_action(
			static::get_table_name( false ) . '_remove_secret_action',
			[ static::class, 'remove_secret_action' ]
		);

	}

	/**
	 * Returns the table name associated with Entity.
	 *
	 * @param bool $prefix Whether to return the prefix or not.
	 *
	 * @return string
	 */
	public static function get_table_name( bool $prefix = true ): string {

		global $wpdb;

		return $prefix ? $wpdb->prefix . static::TABLE : static::TABLE;

	}

	/**
	 * Clears the Entities secret
	 */
	public function clear_secret() {

		$this->secret = null;

	}

	/**
	 * Set the entities secret and schedule removal.
	 *
	 * @param string $timeout How long the secret should be kept in the database for.
	 *
	 * @return string|false
	 */
	public function create_secret( string $timeout = '+10 minutes' ) {

		// Create secret if none set.
		if ( null === $this->secret ) {
			$this->secret = bin2hex( wp_generate_password( 10 ) );
		}

		Utils::schedule_action(
			strtotime( $timeout ),
			'kudos_remove_secret_action',
			[ static::class, $this->id ],
			true
		);

		return wp_hash_password( $this->secret );
	}

	/**
	 * Verify donor's secret
	 *
	 * @param string $hash Hashed version of secret.
	 *
	 * @return bool
	 */
	public function verify_secret( string $hash ): bool {

		return wp_check_password( $this->secret, $hash );

	}

	/**
	 * Returns class as an array using type casting
	 *
	 * @return array
	 */
	public function to_array(): array {

		return get_object_vars( $this );

	}

	/**
	 * Returns the object as a string.
	 *
	 * @return string
	 */
	public function __toString(): string {

		return (string) $this->id;

	}
}
