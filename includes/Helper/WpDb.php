<?php
/**
 * Wrapper class for WordPress' wpdb.
 * Used for dependency injection.
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

use BadMethodCallException;

/**
 * Connect to global $wpdb instance from a proper class.
 *
 * @see https://www.php.net/manual/en/language.oop5.magic.php
 */
class WpDb {

	/**
	 * The wpdb instance.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * WordPress table prefix.
	 *
	 * @var string
	 */
	public string $prefix;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix;
	}

	/**
	 * Wrapper method for $wpdb->get_results()
	 *
	 * @param string $query SQL query string.
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. Default OBJECT.
	 * @return array|object|null Database query results.
	 */
	public function get_results( string $query, string $output = OBJECT ) {
		return $this->wpdb->get_results( $query, $output );
	}

	/**
	 * Pass method calls to wpdb object.
	 *
	 * @throws BadMethodCallException Called when method does not exist.
	 *
	 * @param mixed $name Method name.
	 * @param mixed $arguments Arguments.
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		if ( method_exists( $this->wpdb, $name ) ) {
			return \call_user_func_array( [ $this->wpdb, $name ], $arguments );
		} else {
			throw new BadMethodCallException( esc_html( "Method $name does not exist in the wpdb class" ) );
		}
	}
}
