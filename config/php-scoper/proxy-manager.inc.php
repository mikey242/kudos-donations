<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for the lazy-loading proxy infrastructure:
 *   friendsofphp/proxy-manager-lts  — runtime proxy generation
 *   laminas/laminas-code             — code introspection used by proxy-manager-lts
 *
 * These are required at runtime because Dompdf, MollieApiClient, TwigService and
 * PDFService are declared as lazy services in config/services.php.
 */
return [
	'finders'  => [
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->path(
				[
					'friendsofphp/',
					'laminas/',
				]
			)
			->in( 'vendor' ),
	],
	'patchers' => [],
];
