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
	 * Safely prepares SQL query for execution.
	 *
	 * @param string $query SQL query with placeholders.
	 * @param mixed  ...$args  Array of placeholders values.
	 * @return string Safely prepared SQL query.
	 */
	public function prepare( string $query, ...$args ): string {
		return $this->wpdb->prepare( $query, ...$args );
	}

	/**
	 * Returns value from database.
	 *
	 * @param string|null $query Optional. SQL query. Defaults to null, use the result from the previous query.
	 * @param int         $x Optional. Column of value to return. Indexed from 0. Default 0.
	 * @param int         $y Optional. Row of value to return. Indexed from 0. Default 0.
	 * @return string|null Database query result (as string), or null on failure.
	 */
	public function get_var( ?string $query = null, int $x = 0, int $y = 0 ): ?string {
		return $this->wpdb->get_var( $query, $x, $y );
	}
}
