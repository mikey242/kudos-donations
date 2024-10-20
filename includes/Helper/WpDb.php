<?php
/**
 * Wrapper class for WordPress' wpdb.
 * Used for dependency injection.
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

use BadMethodCallException;

/**
 * Connect to global $wpdb instance from a proper class.
 *
 * @see https://www.php.net/manual/en/language.oop5.magic.php
 *
 * @property \wpdb $wpdb This class provides access to all methods of wpdb.
 *
 * @method string|false prepare(string $query, mixed ...$args) Prepare a SQL query safely, returning the prepared query or false on failure.
 * @method array|object|null get_results(string $query, string $output = OBJECT) Retrieve multiple rows from a SQL query.
 * @method array|object|null get_row(string $query, string $output = OBJECT, int $offset = 0) Retrieve one row from a SQL query.
 * @method mixed get_var(string $query, int $x = 0, int $y = 0) Retrieve one variable (column value) from a SQL query.
 * @method int|false insert(string $table, array $data, array|string|null $format = null) Insert a row into a table, returning the number of affected rows or false on failure.
 * @method int|false update(string $table, array $data, array $where, array|string|null $format = null, array|string|null $where_format = null) Update rows in a table, returning the number of affected rows or false on failure.
 * @method int|false delete(string $table, array $where, array|string|null $where_format = null) Delete rows from a table, returning the number of affected rows or false on failure.
 * @method int|false query(string $query) Execute a raw SQL query, returning the number of rows affected or false on failure.
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
	 * Proxy method calls to the wpdb object.
	 *
	 * @throws BadMethodCallException If the method does not exist on wpdb.
	 *
	 * @param string $name The method name.
	 * @param array  $arguments Arguments passed to the method.
	 * @return mixed The result from the wpdb method.
	 */
	public function __call( string $name, array $arguments ) {
		if ( method_exists( $this->wpdb, $name ) ) {
			return \call_user_func_array( [ $this->wpdb, $name ], $arguments );
		} else {
			throw new BadMethodCallException( esc_html( "Method $name does not exist in the wpdb class" ) );
		}
	}
}
