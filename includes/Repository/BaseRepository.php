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

use IseardMedia\Kudos\Entity\BaseEntity;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Helper\WpDb;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Template for BaseEntity classes.
 *
 * @template TEntity of BaseEntity
 */
abstract class BaseRepository implements LoggerAwareInterface, RepositoryInterface, RepositoryAwareInterface {

	use LoggerAwareTrait;
	use RepositoryAwareTrait;
	use SanitizeTrait;

	protected WpDb $wpdb;
	protected string $table;

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
	 * Defines the common entity schema.
	 */
	private function get_base_column_schema(): array {
		return [
			'id'         => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'wp_post_id' => $this->make_schema_field( FieldType::INTEGER, 'sanitize_text_field' ),
			'title'      => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'created_at' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'updated_at' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}

	/**
	 * Returns the entire column schema.
	 */
	public function get_column_schema(): array {
		return array_merge( $this->get_base_column_schema(), $this->get_additional_column_schema() );
	}

	/**
	 * Get the schema for the repository.
	 */
	abstract public function get_additional_column_schema(): array;

	/**
	 * Get the specified row by id.
	 *
	 * @param int   $id The id to fetch.
	 * @param array $columns The list of columns to return.
	 * @return TEntity|null
	 */
	public function get( int $id, array $columns = [ '*' ] ) {
		$results = $this->query(
			[
				'where'   => [ 'id' => $id ],
				'limit'   => 1,
				'columns' => $columns,
			]
		);

		return $results[0] ?? null;
	}

	/**
	 * Find by specified criteria.
	 *
	 * @param array $criteria The criteria to search by.
	 * @param array $columns The list of columns to return.
	 * @return TEntity[]
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
	 * @return TEntity[] | null     The matching row, or null if not found.
	 */
	public function find_one_by( array $criteria, array $columns = [ '*' ] ): ?BaseEntity {
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
	 * Insert record with provided data.
	 *
	 * @param TEntity $entity The data to insert.
	 * @return int|false The inserted row ID or false on failure.
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
	 */
	public function insert( BaseEntity $entity ) {
		$data = $this->sanitize_data_from_schema( $entity->to_array() );

		$success = $this->wpdb->insert( $this->table, $data );

		if ( ! $success ) {
			return false;
		}

		$id = $this->wpdb->get_insert_id();

		// Generate title if none provided.
		if ( empty( $data['title'] ) && $id ) {
			$entity       = $this->get( $id );
			$formatted_id = Utils::get_id( $entity, static::get_singular_name() );
			$title        = static::get_singular_name() . \sprintf( ' (%1$s)', $formatted_id );
			$this->wpdb->update( $this->table, [ 'title' => $title ], [ 'id' => $id ] );
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
		$data = $this->sanitize_data_from_schema( $entity->to_array() );

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
		if ( $entity->id ?? null ) {
			$result = $this->update( $entity ) ? $entity->id : false;
		} else {
			$result = $this->insert( $entity );
		}

		if ( $this->wpdb->last_error ) {
			$this->logger->error(
				'Failed to update or insert record',
				[
					'last_error' => $this->wpdb->last_error,
					'entity'     => $entity->to_array(),
				]
			);
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
	 * @return int|false  True if the update succeeded.
	 */
	public function patch( int $id, array $data ): bool {

		if ( ! $this->get( $id ) ) {
			throw new \RuntimeException( \sprintf( 'Entity with ID %s not found.', esc_attr( $id ) ) );
		}

		$data = $this->sanitize_data_from_schema( $data );

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

	/**
	 * Main query method for fetching rows.
	 *
	 * @param array $args The args to pass to the query.
	 * @return TEntity[]
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

		$sql = trim( "SELECT $select FROM $this->table {$where['sql']} $order_by $limit_sql $offset_sql" );

		if ( ! empty( $where['params'] ) ) {
			$sql = $this->wpdb->prepare( $sql, ...$where['params'] );
		}

		$results = $this->wpdb->get_results(
			$sql,
			ARRAY_A
		);

		return array_map( fn( $row ) => $this->transform_result( $row ), $results );
	}

	/**
	 * Count results of a specific query.
	 *
	 * @param array $where The WHERE clause.
	 */
	public function count_query( array $where = [] ): int {
		$parts = $this->build_where_clause( $where );
		$sql   = "SELECT COUNT(*) FROM $this->table {$parts['sql']}";

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
	public function cast_types( array $row ): array {
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
						$row[ $key ] = \is_array( $decoded ) || \is_object( $decoded ) ? $decoded : null;
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
	public function sanitize_data_from_schema( array $data ): array {
		$schema  = $this->get_column_schema();
		$allowed = array_intersect_key( $data, $schema );

		foreach ( $allowed as $key => &$value ) {
			if ( '' === $value ) {
				$value = null;
			}

			$callback = $schema[ $key ]['sanitize_callback'] ?? null;
			if ( $callback && null !== $value && \is_callable( $callback ) ) {
				$value = \call_user_func( $callback, $value );
			}
		}

		return $allowed;
	}

	/**
	 * Gets a list of fields for the current repository as defined in get_column_schema().
	 */
	public function get_all_fields(): array {
		return array_keys( $this->get_column_schema() );
	}

	/**
	 * Returns the schema field.
	 *
	 * @param string   $type The type of field (e.g string).
	 * @param callable $sanitize Sanitize callback.
	 */
	protected function make_schema_field( string $type, callable $sanitize ): array {
		return [
			'type'              => $type,
			'sanitize_callback' => $sanitize,
		];
	}

	/**
	 * Creates a new entity from data array.
	 *
	 * @param array $data The raw array data.
	 * @return TEntity
	 */
	public function new_entity( array $data ): BaseEntity {
		$entity_class = $this->get_entity_class();
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
		$data         = $this->cast_types( $row );
		return new $entity_class( $data, $apply_defaults );
	}
}
