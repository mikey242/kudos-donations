<?php
/**
 * Service for re-linking entities based on vendor id and local id.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use Exception;
use InvalidArgumentException;
use IseardMedia\Kudos\Container\SafeLoggerTrait;
use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Repository\BaseRepository;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

class LinkService implements LoggerAwareInterface, RepositoryAwareInterface {

	use SafeLoggerTrait;
	use RepositoryAwareTrait;

	/**
	 * Link local and vendor IDs between two entities.
	 *
	 * @param string $source_repo_class  The repository class name for the entity that holds the references.
	 * @param string $local_key          Column name on the source table holding the local ID (e.g. 'donor_id').
	 * @param string $vendor_key         Column name on the source table holding the vendor ID (e.g. 'vendor_customer_id').
	 * @param string $target_repo_class  The repository class name for the referenced entity (e.g. DonorRepository::class).
	 * @param string $target_vendor_key  Column name on the target table holding its vendor ID (e.g. 'vendor_customer_id').
	 * @param int    $limit         Number of records to process per batch.
	 */
	public function link_entities(
		string $source_repo_class,
		string $local_key,
		string $vendor_key,
		string $target_repo_class,
		string $target_vendor_key,
		int $limit = 100
	): int {
		// Resolve repositories from class names.
		$source_repo = $this->get_repository( $source_repo_class );
		$target_repo = $this->get_repository( $target_repo_class );

		// Validate that the keys exist in the schema.
		$this->validate_keys( $source_repo, [ $local_key, $vendor_key ] );
		$this->validate_keys( $target_repo, [ $target_vendor_key ] );

		// Pre-load vendor ID mappings for better performance.
		$vendor_id_map = $this->build_vendor_id_map( $target_repo, $target_vendor_key );

		$records = $source_repo->query(
			[
				'where'   => [ $local_key => null ],
				'limit'   => $limit,
				'orderby' => 'id',
				'order'   => 'ASC',
			]
		);

		$updates_batch = [];

		foreach ( $records as $record ) {
			$update_needed = false;
			$local_id      = $this->get_property_value( $record, $local_key );
			$vendor_id     = $this->get_property_value( $record, $vendor_key );

			// Case 1: Missing local ID but has vendor ID.
			if ( ! $local_id && $vendor_id ) {
				$target_id = $vendor_id_map[ $vendor_id ] ?? null;

				if ( $target_id ) {
					$this->set_property_value( $record, $local_key, $target_id );
					$update_needed = true;

					$this->logger->info(
						'Linked {target_entity} by {source_entity} vendor id',
						[
							'target_entity' => $target_repo::get_singular_name(),
							'source_entity' => $source_repo::get_singular_name(),
							'vendor_key'    => $target_vendor_key,
							'vendor_id'     => $vendor_id,
							'local_id'      => $local_id,
							'entity_id'     => $record->id,
						]
					);
				} else {

					$this->logger->warning(
						'Could not find {entity} with vendor_id',
						[
							'entity'     => $target_repo::get_singular_name(),
							'vendor_key' => $target_vendor_key,
							'vendor_id'  => $vendor_id,
							'entity_id'  => $record->id,
						]
					);
				}
			}

			if ( $update_needed ) {
				$updates_batch[] = $record;
			}
		}

			// Batch update all modified records.
			$this->perform_batch_updates( $source_repo, $updates_batch );

			return \count( $updates_batch );
	}

	/**
	 * Build a map of vendor IDs to local IDs for efficient lookup.
	 *
	 * @param BaseRepository $repo The repository to check against.
	 * @param string         $vendor_key The unique vendor key.
	 */
	private function build_vendor_id_map( BaseRepository $repo, string $vendor_key ): array {
		$map = [];

		$records = $repo->query(
			[
				'columns' => [ 'id', $vendor_key ],
			]
		);

		foreach ( $records as $record ) {
			$vendor_id = $this->get_property_value( $record, $vendor_key );
			if ( $vendor_id ) {
				$map[ $vendor_id ] = $record->id;
			}
		}

		return $map;
	}

	/**
	 * Perform batch updates.
	 *
	 * @param BaseRepository $repo The repository to check against.
	 * @param array          $entities Array of entities to update.
	 */
	private function perform_batch_updates(
		BaseRepository $repo,
		array $entities
	): void {
		foreach ( $entities as $entity ) {
			try {
				if ( ! $repo->update( $entity ) ) {
					$this->logger->error( 'Failed to update entity', [ 'entity_id' => $entity->id ] );
				}
			} catch ( Exception $e ) {
				$this->logger->error(
					'Exception updating entity',
					[
						'entity_id' => $entity->id,
						'error'     => $e->getMessage(),
					]
				);
			}
		}
	}

	/**
	 * Validate that the specified keys exist in the repository schema.
	 *
	 * @throws InvalidArgumentException Thrown if one or more keys are invalid.
	 *
	 * @param BaseRepository $repo The repository to check against.
	 * @param array          $keys Array of keys to check.
	 */
	private function validate_keys( BaseRepository $repo, array $keys ): void {
		$valid_fields = $repo->get_all_fields();

		foreach ( $keys as $key ) {
			if ( ! \in_array( $key, $valid_fields, true ) ) {
				throw new InvalidArgumentException(
					\sprintf(
						'Invalid key "%s" for repository %s. Valid keys: %s',
						esc_attr( $key ),
						sanitize_html_class( \get_class( $repo ) ),
						esc_attr( implode( ', ', $valid_fields ) )
					)
				);
			}
		}
	}

	/**
	 * Safely get a property value from an entity.
	 *
	 * @throws RuntimeException Thrown if the property does not exist on the entity.
	 *
	 * @param BaseEntity $entity The entity instance.
	 * @param string     $property The property name.
	 * @return mixed
	 */
	private function get_property_value( BaseEntity $entity, string $property ) {
		if ( ! property_exists( $entity, $property ) ) {
			throw new RuntimeException(
				\sprintf( 'Property %s does not exist on entity %s', esc_attr( $property ), sanitize_html_class( \get_class( $entity ) ) )
			);
		}

		return $entity->{$property};
	}

	/**
	 * Safely set a property value on an entity.
	 *
	 * @throws RuntimeException Thrown if the property does not exist on the entity.
	 *
	 * @param BaseEntity $entity The entity instance.
	 * @param string     $property The property name.
	 * @param mixed      $value The property value.
	 */
	private function set_property_value( BaseEntity $entity, string $property, $value ): void {
		if ( ! property_exists( $entity, $property ) ) {
			throw new RuntimeException(
				\sprintf( 'Property %s does not exist on entity %s', esc_attr( $property ), sanitize_html_class( \get_class( $entity ) ) )
			);
		}

		$entity->{$property} = $value;
	}
}
