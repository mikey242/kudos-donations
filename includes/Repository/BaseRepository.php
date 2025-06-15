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
use IseardMedia\Kudos\Helper\WpDb;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class BaseRepository implements LoggerAwareInterface {

	use LoggerAwareTrait;

	protected WpDb $wpdb;
	protected string $table;

	/**
	 * BaseRepository constructor.
	 *
	 * @param WpDb $wpdb For interfacing with the wpdb.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $this->wpdb->table( $this->get_table_name() );
	}

	/**
	 * Required for selecting the correct table.
	 */
	abstract protected function get_table_name(): string;

	/**
	 * Get the schema for the repository.
	 */
	abstract public static function get_column_schema(): array;

	/**
	 * Get the specified row by id.
	 *
	 * @param int $id The id to fetch.
	 */
	public function find( int $id ): ?array {
		return $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ),
			ARRAY_A
		);
	}

	/**
	 * Find by specified criteria.
	 *
	 * @param array $criteria The criteria to search by.
	 * @param array $columns The list of columns to return.
	 */
	public function find_by( array $criteria, array $columns = [ '*' ] ): array {
		$select = implode( ', ', $columns );
		$parts  = $this->build_where_clause( $criteria );

		$sql = "SELECT $select FROM {$this->table} {$parts['where_sql']}";

		return $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, ...$parts['params'] ),
			ARRAY_A
		);
	}

	/**
	 * Find by the post id. This is for legacy access.
	 *
	 * @param int $post_id The post id to search by.
	 */
	public function find_by_post_id( int $post_id ): ?array {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE wp_post_id = %d",
				$post_id
			),
			ARRAY_A
		);
	}

	/**
	 * Insert record with provided data.
	 *
	 * @param array $data The data to insert.
	 * @return int|false The inserted row ID or false on failure.
	 */
	public function insert( array $data ) {
		$success = $this->wpdb->insert( $this->table, $data );

		if ( ! $success ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Update the provided record.
	 *
	 * @param int   $id The id of the record to update.
	 * @param array $data The data to update.
	 */
	public function update( int $id, array $data ): bool {
		return $this->wpdb->update( $this->table, $data, [ 'id' => $id ] ) !== false;
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

	public function query( array $args = [] ): array {
		$select = isset( $args['columns'] ) ? implode( ', ', $args['columns'] ) : '*';
		$where  = [];
		$params = [];

		foreach ( $args['where'] ?? [] as $column => $value ) {
			if ( \is_int( $value ) ) {
				$where[] = "`$column` = %d";
			} elseif ( \is_float( $value ) ) {
				$where[] = "`$column` = %f";
			} else {
				$where[] = "`$column` = %s";
			}
			$params[] = $value;
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$order_by  = isset( $args['orderby'] ) ? 'ORDER BY `' . esc_sql( $args['orderby'] ) . '`' : '';
		$order     = isset( $args['order'] ) ? strtoupper( $args['order'] ) : 'ASC';
		if ( $order_by ) {
			$order_by .= " $order";
		}
		$limit      = isset( $args['limit'] ) ? (int) $args['limit'] : null;
		$offset     = isset( $args['offset'] ) ? (int) $args['offset'] : null;
		$limit_sql  = isset( $limit ) ? "LIMIT $limit" : '';
		$offset_sql = isset( $offset ) ? "OFFSET $offset" : '';

		$sql = trim( "SELECT $select FROM {$this->table} $where_sql $order_by $limit_sql $offset_sql" );

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, ...$params ),
			ARRAY_A
		);

		return array_map( fn( $row ) => $this->cast_types( $row ), $results );
	}

	public function count_query( array $where = [] ): int {
		$parts = $this->build_where_clause( $where );
		$sql   = "SELECT COUNT(*) FROM {$this->table} {$parts['where_sql']}";

		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare( $sql, ...$parts['params'] )
		);
	}

	/**
	 * Generate the WHERE sql clause.
	 *
	 * @param array $criteria The criteria.
	 */
	protected function build_where_clause( array $criteria ): array {
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
			'where_sql' => $clauses ? 'WHERE ' . implode( ' AND ', $clauses ) : '',
			'params'    => $params,
		];
	}

	/**
	 * Cast the provided row as the types specified in get_column_schema.
	 *
	 * @param array $row The row to cast.
	 */
	public static function cast_types( array $row ): array {
		$schema = static::get_column_schema();

		foreach ( $schema as $key => $type ) {
			if ( ! \array_key_exists( $key, $row ) ) {
				continue;
			}

			switch ( $type ) {
				case FieldType::INTEGER:
					$row[ $key ] = (int) $row[ $key ];
					break;
				case FieldType::FLOAT:
					$row[ $key ] = isset( $row[ $key ] ) ? (float) $row[ $key ] : null;
					break;
				case FieldType::BOOLEAN:
					$row[ $key ] = filter_var( $row[ $key ], FILTER_VALIDATE_BOOLEAN );
					break;
				case FieldType::OBJECT:
					$row[ $key ] = $row[ $key ] && json_decode( $row[ $key ], true ) ?? [];
					break;
			}
		}

		return $row;
	}
}
