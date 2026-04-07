<?php

declare(strict_types=1);

/**
 * php-scoper entry point. Merges all per-package configs from config/php-scoper/.
 * Each *.inc.php file returns an array with 'finders' and 'patchers' keys.
 *
 * @see config/php-scoper/
 */

$configs  = array_map( fn( $file ) => require $file, glob( __DIR__ . '/config/php-scoper/*.inc.php' ) ?: [] );
$finders  = ! empty( $configs ) ? array_merge( ...array_column( $configs, 'finders' ) ) : [];
$patchers = ! empty( $configs ) ? array_merge( ...array_column( $configs, 'patchers' ) ) : [];

return [
	'prefix'                  => 'IseardMedia\\Kudos\\ThirdParty',
	'finders'                 => $finders,
	'patchers'                => $patchers,
	'exclude-namespaces'      => [
		'IseardMedia\\Kudos\\',
		'Psr\\',
		'Composer',
	],
	'expose-global-constants' => false,
	'expose-global-functions' => false,
];
