<?php
/**
 * Scoper config.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

use Isolated\Symfony\Component\Finder\Finder;

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$wp_classes = json_decode( file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-classes.json' ), true );
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$wp_functions = json_decode( file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-functions.json' ), true );
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$wp_constants = json_decode( file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-constants.json' ), true );

return [
	'prefix'             => 'IseardMedia\\Kudos_Dependencies',
	'finders'            => [
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.(json|lock)/' )
			->exclude(
				[
					'doc',
					'test',
					'test_old',
					'tests',
					'Tests',
					'vendor-bin',
				]
			)
			->path(
				[
					// Monolog.
					'monolog/',
					'psr/log',

					// DotEnv.
					'symfony/dotenv',

					// Symfony DI.
					'psr/container/',
					'symfony/config/',
					'symfony/filesystem/',
					'symfony/finder/',
					'symfony/service-contracts/',
					'symfony/dependency-injection/',
					'symfony/var-exporter',
					'symfony/',
				]
			)
			->in( 'vendor' ),
		// Main composer.json file so that we can build a classmap.
		Finder::create()
				->append( [ 'composer.json' ] ),
	],
	'exclude-classes'    => $wp_classes,

	'exclude-functions'  => $wp_functions,

	'exclude-constants'  => $wp_constants,

	'exclude-namespaces' => [],
];
