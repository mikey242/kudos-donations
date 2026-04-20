<?php

declare(strict_types=1);

use Composer\InstalledVersions;

/**
 * php-scoper entry point. Merges all per-package configs from config/php-scoper/.
 * Each *.inc.php file returns an array with 'finders' and 'patchers' keys.
 *
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 */

$configs  = array_map( fn( $file ) => require $file, glob( __DIR__ . '/config/php-scoper/*.inc.php' ) ?: [] );
$finders  = ! empty( $configs ) ? array_merge( ...array_column( $configs, 'finders' ) ) : [];
$patchers = ! empty( $configs ) ? array_merge( ...array_column( $configs, 'patchers' ) ) : [];

// Remove non-essential files from finders.
$exclude = [ 'test', 'tests', 'Test', 'Tests', 'bin' ];
$notName = [ '*.md', 'CHANGELOG*', 'LICENSE*', 'README*', 'CREDITS', 'phpunit.xml', 'composer.json' ];

foreach ( $finders as $finder ) {
	$finder->exclude( $exclude )->notName( $notName );
}

return [
	'prefix'                  => 'IseardMedia\\Kudos\\ThirdParty',
	'php-version'             => '7.4',
	'finders'                 => $finders,
	'patchers'                => $patchers,
	'exclude-namespaces'      => [
		'IseardMedia\\Kudos\\',
		'Psr\\',
		'Composer',
	],
	'exclude-classes'         => [ InstalledVersions::class ],
	'exclude-files'           => [ 'vendor/composer/InstalledVersions.php' ],
	'expose-global-constants' => false,
	'expose-global-functions' => false,
];
