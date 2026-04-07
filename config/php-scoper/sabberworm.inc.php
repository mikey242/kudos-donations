<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for sabberworm/php-css-parser.
 *
 * Used transitively by dompdf/dompdf for CSS stylesheet parsing.
 */
return [
	'finders'  => [
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->path( 'sabberworm/' )
			->in( 'vendor' ),
	],
	'patchers' => [],
];
