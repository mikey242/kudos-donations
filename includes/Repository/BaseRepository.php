<?php
/**
 * Base repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Helper\WpDb;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class BaseRepository implements LoggerAwareInterface, RepositoryInterface, RepositoryAwareInterface {

	use LoggerAwareTrait;
	use RepositoryAwareTrait;

	protected WpDb $wpdb;
	protected string $table;

	/**
	 * Field constants.
	 */
	public const ID         = 'id';
	public const POST_ID    = 'wp_post_id';
	public const TITLE      = 'title';
	public const CREATED_AT = 'created_at';
	public const UPDATED_AT = 'updated_at';

	/**
	 * BaseRepository constructor.
	 *
	 * @param WpDb $wpdb For interfacing with the wpdb.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $this->wpdb->table( static::TABLE_NAME );
	}

	/**
	 * The singular name.
	 */
	abstract protected function get_singular_name(): string;

	/**
	 * The plural name.
	 */
	abstract protected function get_plural_name(): string;

	/**
	 * Get the schema for the repository.
	 */
	abstract public function get_column_schema(): array;

	/**
	 * Get the specified row by id.
	 *
	 * @param int $id The id to fetch.
	 */
	public function find( int $id ): ?array {
		$results = $this->query(
			[
				'where' => [ 'id' => $id ],
				'limit' => 1,
			]
		);

		return $results[0] ?? null;
	}

	/**
	 * Find by specified criteria.
	 *
	 * @param array $criteria The criteria to search by.
	 * @param array $columns The list of columns to return.
	 */
	public function find_by( array $criteria, array $columns = [ '*' ] ): array {
		return $this->query(
			[
				'where'   => $criteria,
				'columns' => $columns,
			]
		);
	}

	/**
	 * Find a single row by specified criteria.
	 *
	 * @param array $criteria Key-value pairs for WHERE clause.
	 * @param array $columns  List of columns to return. Defaults to all.
	 * @return array|null     The matching row, or null if not found.
	 */
	public function find_one_by( array $criteria, array $columns = [ '*' ] ): ?array {
		$results = $this->query(
			[
				'where'   => $criteria,
				'columns' => $columns,
				'limit'   => 1,
			]
		);

		return $results[0] ?? null;
	}

	/**
	 * Find by the post id. This is for legacy access.
	 *
	 * @param int $post_id The post id to search by.
	 */
	public function find_by_post_id( int $post_id ): ?array {
		$results = $this->query(
			[
				'where' => [ 'wp_post_id' => $post_id ],
				'limit' => 1,
			]
		);

		return $results[0] ?? null;
	}

	/**
	 * Insert record with provided data.
	 *
	 * @param array $data The data to insert.
	 * @return int|false The inserted row ID or false on failure.
	 */
	private function insert( array $data ) {
		$success = $this->wpdb->insert( $this->table, $data );

		if ( ! $success ) {
			return false;
		}

		$id = $this->wpdb->insert_id;

		// Generate title if none provided.
		if ( empty( $data[ self::TITLE ] ) ) {
			$args         = $this->find( $id );
			$formatted_id = Utils::get_id( $args, static::get_singular_name() );
			$title        = static::get_singular_name() . \sprintf( ' (%1$s)', $formatted_id );
			$this->wpdb->update( $this->table, [ self::TITLE => $title ], [ 'id' => $id ] );
		}

		return $id;
	}

	/**
	 * Update the provided record.
	 *
	 * @param int   $id The id of the record to update.
	 * @param array $data The data to update.
	 */
	private function update( int $id, array $data ): bool {
		$data[ self::UPDATED_AT ] = current_time( 'mysql', true );
		return $this->wpdb->update( $this->table, $data, [ 'id' => $id ] ) !== false;
	}

	/**
	 * Upsert a record (insert or update depending on presence of ID).
	 *
	 * @param array $data The data to upsert.
	 * @return int|false The inserted or updated row ID, or false on failure.
	 */
	public function upsert( array $data ) {
		$prepared_data = $this->sanitize_data_from_schema( $data );
		if ( isset( $prepared_data[ self::ID ] ) && $prepared_data[ self::ID ] ) {
			$id = (int) $prepared_data[ self::ID ];
			unset( $prepared_data[ self::ID ] );

			return $this->update( $id, $prepared_data ) ? $id : false;
		}

		return $this->insert( $prepared_data );
	}

	/**
	 * Delete the record.
	 *
	 * @param int $id id of the record to delete.
	 */
	public function delete( int $id ): bool {
		return $this->wpdb->delete( $this->table, [ 'id' => $id ] ) !== false;
	}

	/**
	 * Return all records for this repository.
	 *
	 * @param array $columns The list of columns to return.
	 */
	public function all( array $columns = [ '*' ] ): array {
		return $this->query( [ 'columns' => $columns ] );
	}

	/**
	 * Main query method for fetching rows.
	 *
	 * @param array $args The args to pass to the query.
	 *
	 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	 */
	public function query( array $args = [] ): array {
		$select = isset( $args['columns'] ) ? implode( ', ', $args['columns'] ) : '*';

		$where = $this->build_where_clause( $args['where'] ?? [] );

		$order_by = isset( $args['orderby'] ) ? 'ORDER BY `' . esc_sql( $args['orderby'] ) . '`' : '';
		$order    = isset( $args['order'] ) ? strtoupper( $args['order'] ) : 'ASC';
		if ( $order_by ) {
			$order_by .= " $order";
		}
		$limit      = isset( $args['limit'] ) ? (int) $args['limit'] : null;
		$offset     = isset( $args['offset'] ) ? (int) $args['offset'] : null;
		$limit_sql  = isset( $limit ) ? "LIMIT $limit" : '';
		$offset_sql = isset( $offset ) ? "OFFSET $offset" : '';

		$sql = trim( "SELECT $select FROM {$this->table} {$where['sql']} $order_by $limit_sql $offset_sql" );

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, ...$where['params'] ),
			ARRAY_A
		);

		return array_map( fn( $row ) => $this->cast_types( $row ), $results );
	}

	/**
	 * Count results of a specific query.
	 *
	 * @param array $where The WHERE clause.
	 */
	public function count_query( array $where = [] ): int {
		$parts = $this->build_where_clause( $where );
		$sql   = "SELECT COUNT(*) FROM {$this->table} {$parts['sql']}";

		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare( $sql, ...$parts['params'] )
		);
	}

	/**
	 * Generate the WHERE sql clause.
	 *
	 * @param array $criteria The criteria.
	 */
	private function build_where_clause( array $criteria ): array {
		$clauses = [];
		$params  = [];

		foreach ( $criteria as $column => $value ) {
			if ( \is_int( $value ) ) {
				$clauses[] = "`$column` = %d";
			} elseif ( \is_float( $value ) ) {
				$clauses[] = "`$column` = %f";
			} else {
				$clauses[] = "`$column` = %s";
			}
			$params[] = $value;
		}

		return [
			'sql'    => $clauses ? 'WHERE ' . implode( ' AND ', $clauses ) : '',
			'params' => $params,
		];
	}

	/**
	 * Cast the provided row as the types specified in get_column_schema.
	 *
	 * @param array $row The row to cast.
	 */
	protected function cast_types( array $row ): array {
		$schema = $this->get_column_schema();

		foreach ( $schema as $key => $args ) {
			if ( ! \array_key_exists( $key, $row ) ) {
				continue;
			}

			$value = $row[ $key ];
			$type  = $args['type'] ?? null;

			switch ( $type ) {
				case FieldType::INTEGER:
					$row[ $key ] = is_numeric( $value ) ? (int) $value : null;
					break;

				case FieldType::FLOAT:
					$row[ $key ] = '' === $value || null === $value ? null : (float) $value;
					break;

				case FieldType::BOOLEAN:
					$row[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
					break;

				case FieldType::OBJECT:
					if ( \is_array( $value ) || \is_object( $value ) ) {
						// Already decoded.
						$row[ $key ] = $value;
					} elseif ( \is_string( $value ) && $this->is_valid_json( $value ) ) {
						$decoded     = json_decode( $value, true );
						$row[ $key ] = \is_array( $decoded ) || \is_object( $decoded ) ? $decoded : $value;
					}
					break;

				case FieldType::STRING:
				default:
					$row[ $key ] = \is_scalar( $value ) ? (string) $value : '';
					break;
			}
		}

		return $row;
	}

	/**
	 * Prepares values for insertion into db.
	 *
	 * @param array $data The data for insertion.
	 */
	private function sanitize_data_from_schema( array $data ): array {
		$schema = $this->get_column_schema();

		foreach ( $schema as $key => $args ) {
			if ( ! \array_key_exists( $key, $data ) ) {
				continue;
			}

			$value = $data[ $key ];

			// Normalize empty strings to null.
			if ( '' === $value ) {
				$value = null;
			}

			// Sanitize if possible.
			$value = $this->maybe_sanitize( $args['sanitize_callback'] ?? null, $value );

			$data[ $key ] = $value;
		}

		return $data;
	}

	/**
	 * Checks if value is not null before sanitizing.
	 *
	 * @param callable $callback The sanitize callback.
	 * @param mixed    $value The value to sanitize.
	 * @return mixed|null
	 */
	private function maybe_sanitize( callable $callback, $value ) {
		if ( null === $value ) {
			return null;
		}
		return \is_callable( $callback ) ? \call_user_func( $callback, $value ) : $value;
	}

	/**
	 * Check if a string contains valid JSON.
	 *
	 * @param string $json The string to check.
	 */
	private function is_valid_json( string $json ): bool {
		json_decode( $json );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Returns the schema field.
	 *
	 * @param string   $type The type of field (e.g string).
	 * @param mixed    $default_value The default value for this field.
	 * @param callable $sanitize Sanitize callback.
	 */
	protected function make_schema_field( string $type, $default_value, callable $sanitize ): array {
		return [
			'type'              => $type,
			'default'           => $default_value,
			'sanitize_callback' => $sanitize,
		];
	}
}
