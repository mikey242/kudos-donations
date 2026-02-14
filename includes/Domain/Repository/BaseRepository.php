<?php
/**
 * Base repository.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Schema\BaseSchema;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Helper\WpDb;

/**
 * Template for BaseEntity classes.
 *
 * @template TEntity of BaseEntity
 */
abstract class BaseRepository implements RepositoryInterface, RepositoryAwareInterface {

	use RepositoryAwareTrait;

	protected WpDb $wpdb;
	protected string $table;
	private BaseSchema $schema;

	/**
	 * BaseRepository constructor.
	 *
	 * @param WpDb       $wpdb For interfacing with the wpdb.
	 * @param BaseSchema $schema The schema for this repository.
	 */
	public function __construct( WpDb $wpdb, BaseSchema $schema ) {
		$this->wpdb   = $wpdb;
		$this->table  = $this->wpdb->table( static::get_table_name() );
		$this->schema = $schema;
	}

	/**
	 * Returns the table name.
	 */
	abstract public static function get_table_name(): string;

	/**
	 * The singular name.
	 */
	abstract public static function get_singular_name(): string;

	/**
	 * The plural name.
	 */
	abstract public static function get_plural_name(): string;

	/**
	 * Gets the associated entity class.
	 *
	 * @return class-string<TEntity>
	 */
	abstract protected function get_entity_class(): string;

	/**
	 * Get the specified row by id.
	 *
	 * @param int           $id The id to fetch.
	 * @param array<string> $columns List of columns to return. Defaults to all.
	 * @return TEntity|null
	 */
	public function get( int $id, array $columns = [ '*' ] ) {
		$results = $this->query(
			[
				'where'   => [ 'id' => $id ],
				'limit'   => 1,
				'columns' => array_values( $columns ),
			]
		);

		return $results[0] ?? null;
	}

	/**
	 * Find by specified criteria.
	 *
	 * @param array<string, mixed> $criteria Key-value pairs for WHERE clause.
	 * @param array<string>        $columns List of columns to return. Defaults to all.
	 * @return TEntity[]
	 */
	public function find_by( array $criteria, array $columns = [ '*' ] ): array {
		return $this->query(
			[
				'where'   => $criteria,
				'columns' => array_values( $columns ),
			]
		);
	}

	/**
	 * Find a single row by specified criteria.
	 *
	 * @param array<string, mixed> $criteria Key-value pairs for WHERE clause.
	 * @param array<string>        $columns  List of columns to return. Defaults to all.
	 * @return TEntity | null     The matching row, or null if not found.
	 */
	public function find_one_by( array $criteria, array $columns = [ '*' ] ): ?BaseEntity {
		$results = $this->query(
			[
				'where'   => $criteria,
				'columns' => array_values( $columns ),
				'limit'   => 1,
			]
		);

		return $results[0] ?? null;
	}

	/**
	 * Insert record with provided data.
	 *
	 * @param TEntity $entity The data to insert.
	 * @return int|false The inserted row ID or false on failure.
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
	 */
	public function insert( BaseEntity $entity ) {
		$data = $this->schema->sanitize_data_from_schema( $entity->to_array() );

		$success = $this->wpdb->insert( $this->table, $data );

		if ( false === $success ) {
			return false;
		}

		$id = $this->wpdb->get_insert_id();

		// Generate title if none provided.
		if ( empty( $data['title'] ) && $id ) {
			$entity = $this->get( $id );
			if ( $entity ) {
				$formatted_id = Utils::get_id( $entity, static::get_singular_name() );
				$title        = static::get_singular_name() . \sprintf( ' (%1$s)', $formatted_id );
				$this->wpdb->update( $this->table, [ 'title' => $title ], [ 'id' => $id ] );
			}
		}

		return $id;
	}

	/**
	 * Update the provided record.
	 *
	 * @throws \InvalidArgumentException Thrown if id missing.
	 *
	 * @param TEntity $entity The data to update.
	 */
	public function update( BaseEntity $entity ): bool {
		$data = $this->schema->sanitize_data_from_schema( $entity->to_array() );

		if ( ! isset( $data['id'] ) ) {
			throw new \InvalidArgumentException( 'Cannot update entity without ID.' );
		}

		$id = (int) $data['id'];
		unset( $data['id'] );

		$data['updated_at'] = current_time( 'mysql', true );

		return $this->wpdb->update( $this->table, $data, [ 'id' => $id ] ) !== false;
	}

	/**
	 * Save a record (insert or update depending on presence of ID).
	 *
	 * @param TEntity $entity The data to upsert.
	 * @return int|false The inserted or updated row ID, or false on failure.
	 */
	public function upsert( BaseEntity $entity ) {
		if ( isset( $entity->id ) ) {
			$result = $this->update( $entity ) ? $entity->id : false;
		} else {
			$result = $this->insert( $entity );
		}

		if ( $this->wpdb->last_error ) {
			// phpcs:disable: WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $this->wpdb->last_error );
		}

		return $result;
	}

	/**
	 * Patch specific fields of an existing entity.
	 *
	 * @throws \RuntimeException If entity with provided id doesn't exist.
	 *
	 * @param int   $id   The ID of the entity to update.
	 * @param array $data The partial data to update.
	 * @return bool  True if the update succeeded.
	 */
	public function patch( int $id, array $data ): bool {

		if ( ! $this->get( $id ) ) {
			throw new \RuntimeException( \sprintf( 'Entity with ID %s not found.', esc_attr( (string) $id ) ) );
		}

		$data = $this->schema->sanitize_data_from_schema( $data );

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
	 * @param list<string> $columns The list of columns to return.
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
	 * @return TEntity[]
	 *
	 * @phpstan-param array{
	 * columns?: list<string>|array<string>,
	 * where?: array<string, scalar|null>,
	 * orderby?: string,
	 * order?: 'ASC'|'DESC',
	 * limit?: int,
	 * offset?: int
	 * } $args
	 */
	public function query( array $args = [] ): array {
		$valid_fields = $this->get_all_fields();

		if ( isset( $args['columns'] ) && [ '*' ] !== $args['columns'] ) {
			$args['columns'] = array_filter( $args['columns'], fn( $col ) => \in_array( $col, $valid_fields, true ) );
		}
		$select = isset( $args['columns'] ) ? implode( ', ', $args['columns'] ) : '*';

		$where = $this->build_where_clause( $args['where'] ?? [] );

		$order_by = '';
		if ( isset( $args['orderby'] ) && \in_array( $args['orderby'], $valid_fields, true ) ) {
			$order    = isset( $args['order'] ) && 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';
			$order_by = "ORDER BY `{$args['orderby']}` $order";
		}
		$limit      = $args['limit'] ?? null;
		$offset     = $args['offset'] ?? null;
		$limit_sql  = isset( $limit ) ? 'LIMIT ' . absint( $limit ) : '';
		$offset_sql = isset( $offset ) ? 'OFFSET ' . absint( $offset ) : '';

		$sql = trim( "SELECT $select FROM $this->table {$where['sql']} $order_by $limit_sql $offset_sql" );

		if ( ! empty( $where['params'] ) ) {
			$sql = $this->wpdb->prepare( $sql, ...$where['params'] );
		}

		$results = $this->wpdb->get_results(
			(string) $sql,
			ARRAY_A
		);

		if ( ! \is_array( $results ) ) {
			$results = [];
		}

		return array_map( fn( array $row ) => $this->transform_result( $row ), $results );
	}

	/**
	 * Count results of a specific query.
	 *
	 * @param array<string, scalar|null> $where The WHERE clause.
	 */
	public function count_query( array $where = [] ): int {
		$parts = $this->build_where_clause( $where );
		$sql   = "SELECT COUNT(*)     FROM $this->table {$parts['sql']}";

		if ( ! empty( $parts['params'] ) ) {
			$sql = $this->wpdb->prepare( $sql, ...$parts['params'] );

			if ( false === $sql ) {
				return 0;
			}
		}

		return (int) $this->wpdb->get_var( (string) $sql );
	}

	/**
	 * Generate the WHERE sql clause.
	 *
	 * @param array<string, scalar|null> $criteria The criteria.
	 */
	private function build_where_clause( array $criteria ): array {
		$clauses = [];
		$params  = [];

		foreach ( $criteria as $column => $value ) {
			if ( null === $value ) {
				$clauses[] = "`$column` IS NULL";
			} elseif ( \is_int( $value ) ) {
				$clauses[] = "`$column` = %d";
				$params[]  = $value;
			} elseif ( \is_float( $value ) ) {
				$clauses[] = "`$column` = %f";
				$params[]  = $value;
			} else {
				$clauses[] = "`$column` = %s";
				$params[]  = $value;
			}
		}

		return [
			'sql'    => $clauses ? 'WHERE ' . implode( ' AND ', $clauses ) : '',
			'params' => $params,
		];
	}

	/**
	 * Gets a list of fields for the current repository as defined in get_column_schema().
	 */
	public function get_all_fields(): array {
		return array_keys( $this->schema->get_column_schema() );
	}

	/**
	 * Creates a new entity from data array.
	 *
	 * @param array $data The raw array data.
	 * @return TEntity
	 */
	public function new_entity( array $data ): BaseEntity {
		$entity_class = $this->get_entity_class();
		/** @psalm-suppress UnsafeInstantiation */
		return new $entity_class( $data );
	}

	/**
	 * Convert result into entity.
	 *
	 * @param array $row The result from the db.
	 * @param bool  $apply_defaults Whether to apply default values or not.
	 * @return TEntity
	 */
	private function transform_result( array $row, bool $apply_defaults = true ) {
		$entity_class = $this->get_entity_class();
		$data         = $this->schema->cast_types( $row );
		/** @psalm-suppress UnsafeInstantiation */
		return new $entity_class( $data, $apply_defaults );
	}

	/**
	 * Return the linked schema instance.
	 */
	public function get_schema(): BaseSchema {
		return $this->schema;
	}
}
