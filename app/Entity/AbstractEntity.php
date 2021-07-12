<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Throwable;

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
	 *
	 * @since   2.0.0
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
	 * Removes the secret for the current entity where
	 * it matches the provided id
	 *
	 * @param string $secret The secret as stored in the database.
	 *
	 * @return bool|int
	 */
	public static function remove_secret_action( string $secret ) {

		if ( $secret ) {
			$mapper = new MapperService(new LoggerService());
			/** @var AbstractEntity $entity */
			$entity = $mapper
				->get_repository(static::class)
				->get_one_by( [ 'secret' => $secret ] );
			if ( ! $entity ) {
				return false;
			}
			$entity->clear_secret();

			return $mapper->save( $entity, false );
		}

		return false;
	}

	/**
	 * Clears the Entities secret
	 *
	 * @since   2.0.0
	 */
	public function clear_secret() {

		$this->secret = null;

	}

	/**
	 * Set the donor's secret
	 *
	 * @param string $timeout How long the secret should be kept in the database for.
	 *
	 * @return string|false
	 * @throws \Exception
	 * @since   2.0.0
	 */
	public function create_secret( string $timeout = '+10 minutes' ) {

		$table = static::get_table_name( false );

		// Create secret if none set.
		if ( null === $this->secret ) {
			$this->secret = bin2hex( random_bytes( 10 ) );
		}

		Utils::schedule_action( strtotime( $timeout ), $table . '_remove_secret_action', [ $this->secret ], true );

		return wp_hash_password( $this->secret );


	}

	/**
	 * Verify donor's secret
	 *
	 * @param string $hash Hashed version of secret.
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function verify_secret( string $hash ): bool {

		return wp_check_password( $this->secret, $hash );

	}

	/**
	 * Returns class as an array using type casting
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function to_array(): array {

		return get_object_vars( $this );

	}

	/**
	 * Returns the object as a string.
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public function __toString(): string {

		return (string) $this->id;

	}
}
