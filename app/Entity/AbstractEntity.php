<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Exceptions\EntityException;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Throwable;

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
	 * @var string
	 */
	public $secret;

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

	public static function create_hooks() {

		add_action( static::get_table_name( false ) . "_remove_secret_action",
			[ static::class, "remove_secret_action" ],
			10,
			2 );

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
			try {
				if ( property_exists( static::class, $property ) ) {
					$this->$property = $value;
				} else {
					throw new EntityException( 'Property does not exist!', 0, $property, static::class );
				}
			} catch ( EntityException $e ) {
				$logger = new LoggerService();
				$logger->warning( 'Error setting property.', [ "message" => $e->getMessage() ] );

			}

		}
	}

	/**
	 * Returns the table name associated with Entity
	 *
	 * @param bool $prefix Whether to return the prefix or not
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public static function get_table_name( bool $prefix = true ) {

		global $wpdb;

		return $prefix ? $wpdb->prefix . static::TABLE : static::TABLE;

	}

	/**
	 * Set the donor's secret
	 *
	 * @param string $timeout
	 *
	 * @return string|false
	 * @since   2.0.0
	 */
	public function create_secret( $timeout = '+10 minutes' ) {

		$logger = new LoggerService();
		$table  = static::get_table_name( false );

		try {

			// Schedule for secret to be removed after timeout
			if ( class_exists( 'ActionScheduler' ) ) {

				// Remove existing action if exists
				as_unschedule_action( $table . "_remove_secret_action", [ $this->id ] );
				$timestamp = strtotime( $timeout );

				// Create new action to remove secret
				as_schedule_single_action( $timestamp, $table . "_remove_secret_action", [ $this->id ] );
				$logger->debug( sprintf( "Action %s_remove_secret_action scheduled", $table ),
					[
						'datetime' => wp_date( 'Y-m-d H:i:s', $timestamp ),
					] );
			}

			// Create secret if none set
			if ( null === $this->secret ) {
				$this->secret = bin2hex( random_bytes( 10 ) );
			}

			return password_hash( $this->secret, PASSWORD_DEFAULT );

		} catch ( Throwable $e ) {

			$logger->error( sprintf( 'Unable to create secret for %s. ', $table ) . $e->getMessage(),
				[ 'id' => $this->id ] );

			return false;

		}

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
	 * Removes the secret for the current entity where
	 * it matches the provided id
	 *
	 * @param $id
	 *
	 * @return bool|int
	 */
	public static function remove_secret_action( $id ) {

		if ( $id ) {
			$mapper = new MapperService( static::class );
			/** @var AbstractEntity $entity */
			$entity = $mapper->get_one_by( [ 'id' => $id ] );
			if ( ! $entity ) {
				return false;
			}
			$entity->clear_secret();

			return $mapper->save( $entity );
		}

		return false;
	}

	/**
	 * Verify donor's secret
	 *
	 * @param string $hash
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function verify_secret( string $hash ) {

		return password_verify( $this->secret, $hash );

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