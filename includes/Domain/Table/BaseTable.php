<?php
/**
 * Base Table.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Table;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Helper\WpDb;

abstract class BaseTable implements ActivationAwareInterface {

	/**
	 * Our wpdb wrapper class.
	 *
	 * @var WpDb
	 */
	private WpDb $wpdb;

	/**
	 * Table name, without the global table prefix.
	 *
	 * @var   string
	 */
	protected string $name = '';

	/**
	 * Optional description.

	 * @var   string
	 */
	protected string $description = '';

	/**
	 * Table schema.
	 *
	 * @var   string
	 */
	protected string $schema = '';

	/**
	 * Add the WpDb wrapper class as a property.
	 *
	 * @param WpDb $wpdb The wpdb wrapper class.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb   = $wpdb;
		$this->schema = $this->get_schema();
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation(): void {
		static::create_table();
	}

	/**
	 * Return the table name, without the global table prefix.
	 */
	abstract public static function get_name(): string;

	/**
	 * Setup this database table.
	 */
	abstract protected function get_schema(): string;

	/**
	 * Creates the table in the database.
	 */
	public function create_table(): void {
		$table_name = $this->wpdb->table( static::get_name() );
		$charset    = $this->wpdb->get_charset_collate();
		$schema     = $this->schema;
		$this->wpdb->run_dbdelta( "CREATE TABLE $table_name ( $schema ) $charset" );
	}
}
