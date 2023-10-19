<?php
/**
 * Wrapper class for WordPress' wpdb.
 * Used for dependency injection.
 *
 * @source https://gist.github.com/szepeviktor/ddb1bfd12d93accd318cc081637956ec
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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
	 * Get a property.
	 *
	 * @see https://codex.wordpress.org/Class_Reference/wpdb#Class_Variables
	 *
	 * @param string $name The property name to get.
	 * @return mixed
	 */
	public function __get( string $name ) {
		global $wpdb;

		return $wpdb->$name;
	}

	/**
	 * Noop on set.
	 *
	 * @param string $name The property name to set.
	 * @param mixed  $value The property value.
	 */
	public function __set( string $name, $value ): void {
	}

	/**
	 * Execute a method.
	 *
	 * @see https://www.php.net/manual/en/language.oop5.overloading.php#object.call
	 *
	 * @param string $name Callable name.
	 * @param array  $arguments Arguments to pass.
	 * @return mixed
	 */
	public function __call( string $name, array $arguments ) {
		global $wpdb;

		return \call_user_func_array( [ $wpdb, $name ], $arguments );
	}
}
