<?php
/**
 * Used to manage and create campaign fixtures.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use Faker\Factory;
use Faker\Generator;
use IseardMedia\Kudos\Entity\BaseEntity;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Repository\RepositoryInterface;
use WP_CLI;
use WP_CLI\ExitException;

abstract class BaseFixtures {
	protected WpDb $wpdb;
	protected RepositoryInterface $repository;
	protected Generator $faker;

	/**
	 * Add wpdb property.
	 */
	public function __construct() {
		$this->wpdb  = new WpDb();
		$this->faker = Factory::create();
	}

	/**
	 * Generate or delete fixture data for this entity.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many entities to create. Default 1.
	 *
	 * [--delete]
	 * : Delete all existing entities instead of creating.
	 *
	 * [--force]
	 * : Force delete without confirmation (only used with --delete).
	 *
	 * ## EXAMPLES
	 *
	 * wp kudos:fixtures campaign --count=5
	 * wp kudos:fixtures campaign --delete --force
	 *
	 * @throws ExitException Thrown if entity not created.
	 *
	 * @param mixed $args Arguments.
	 * @param mixed $assoc_args Associative arguments.
	 */
	public function __invoke( $args, $assoc_args ): void {
		WP_CLI::log( WP_CLI::colorize( '%3%k-----===========-----%N' ) );
		WP_CLI::log( WP_CLI::colorize( '%3%k ' . gmdate( 'H:i:s d-m-Y' ) . ' %N' ) );
		WP_CLI::log( '' );

		// Before hook.
		$this->before();

		if ( ! empty( $assoc_args['delete'] ) ) {
			$this->delete_all( $assoc_args );
		}

		$count         = $assoc_args['count'] ?? 1;
		$singular_name = $this->repository::get_singular_name();
		$plural_name   = $this->repository::get_plural_name();

		WP_CLI::log( "Adding $count $plural_name" );
		WP_CLI::log( '' );

		$created = [];
		for ( $x = 0; $x < $count; $x++ ) {
			$entity             = $this->generate_random_entity();
			$entity->created_at = $this->faker->dateTimeThisDecade()->format( 'Y-m-d H:i:s' );
			if ( empty( $entity ) ) {
				WP_CLI::warning( "Skipping $singular_name: no valid data." );
				continue;
			}
			$result = $this->repository->insert( $entity );
			if ( ! $result ) {
				WP_CLI::halt( 1 );
			}
			$created[] = $result;
			WP_CLI::log( "Created $singular_name: " . $entity->title );
		}

		// After hook.
		$this->after( $created );

		WP_CLI::log( '' );
		WP_CLI::log( WP_CLI::colorize( '%3%k ' . gmdate( 'H:i:s d-m-Y' ) . ' %N' ) );
		WP_CLI::log( WP_CLI::colorize( '%3%k-----===========-----%N' ) );
	}

	/**
	 * Generates random entity data.
	 */
	abstract protected function generate_random_entity(): BaseEntity;

	/**
	 * Optional pre-processing hook before entities created.
	 */
	protected function before(): void {
		// Default: no-op.
	}

	/**
	 * Optional post-processing hook after all entities are created.
	 *
	 * @param array $created_entities The entities returned from `save()`.
	 */
	protected function after( array $created_entities ): void {
		// Default: no-op.
	}

	/**
	 * Deletes all entities in the database.
	 *
	 * @param array $assoc_args Associative arguments.
	 */
	protected function delete_all( array $assoc_args ): void {
		$plural_name = $this->repository::get_plural_name();
		if ( empty( $assoc_args['force'] ) ) {
			WP_CLI::confirm( "Are you sure you want to delete all $plural_name?" );
		}

		/** @var BaseEntity[] $all */
		$all = $this->repository->all();

		if ( empty( $all ) ) {
			WP_CLI::warning( "No $plural_name to delete." );
			return;
		}

		foreach ( $all as $entity ) {
			$this->repository->delete( $entity->id );
		}

		WP_CLI::success( \count( $all ) . " $plural_name deleted." );
	}

	/**
	 * Pick a random value from a weighted distribution.
	 *
	 * @param array<string, int> $weighted_map Array of value => weight.
	 * @return string Selected key.
	 */
	protected function pick_weighted( array $weighted_map ): string {
		$total = array_sum( $weighted_map );
		$rand  = wp_rand( 1, $total );
		$accum = 0;

		foreach ( $weighted_map as $value => $weight ) {
			$accum += $weight;
			if ( $rand <= $accum ) {
				return $value;
			}
		}

		// Fallback.
		return array_key_first( $weighted_map );
	}
}
