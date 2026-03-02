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
 * @mixin \wpdb
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
		if ( \is_callable( [ $this->wpdb, $name ] ) ) {
			return \call_user_func_array( [ $this->wpdb, $name ], $arguments );
		} else {
			throw new BadMethodCallException( esc_html( "Method '$name' does not exist in the wpdb class" ) );
		}
	}

	/**
	 * Proxy property access to the wpdb object.
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	public function __get( string $name ) {
		if ( property_exists( $this->wpdb, $name ) ) {
			return $this->wpdb->$name;
		}
		return null;
	}

	/**
	 * Get the table with prefix.
	 *
	 * @param string $name Table name without prefix.
	 */
	public function table( string $name ): string {
		return $this->prefix . $name;
	}

	/**
	 * Check if the provided table exists.
	 *
	 * @param string $base_table_name The name of the table (without prefix).
	 */
	public function table_exists( string $base_table_name ): bool {
		return (bool) $this->get_var(
			$this->prepare(
				'SHOW TABLES LIKE %s',
				$this->table( $base_table_name )
			)
		);
	}

	/**
	 * Ensures required files is included and runs dbDelta on provided sqp.
	 *
	 * @param string $sql The sqp statement.
	 */
	public function run_dbdelta( string $sql ): void {
		if ( ! \function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		dbDelta( $sql );
	}

	/**
	 * Returns the last inserted id.
	 */
	public function get_insert_id(): int {
		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Check if a column exists in the provided table.
	 *
	 * @param string $base_table_name The name of the table (without prefix).
	 * @param string $column The column name.
	 */
	public function column_exists( string $base_table_name, string $column ): bool {
		$table = $this->table( $base_table_name );
		return (bool) $this->get_var(
			$this->prepare(
				'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = %s',
				$table,
				$column
			)
		);
	}

	/**
	 * Truncate a custom plugin table.
	 *
	 * @param string $table_name Table name without prefix.
	 */
	public function truncate_table( string $table_name ): void {
		$wpdb = $this->wpdb;
		$wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %i', $wpdb->prefix . $table_name ) );
	}
}
