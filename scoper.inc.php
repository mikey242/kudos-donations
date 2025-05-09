<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

$finders = [
	Finder::create()
	      ->files()
	      ->ignoreVCS(true)
	      ->ignoreDotFiles(true)
	      ->path([
			  'mollie/',
		      'monolog/',
		      'dompdf/'
	      ])
	      ->in('vendor'),
];

return [
	'prefix' => 'IseardMedia\\Kudos\\ThirdParty',
	'finders' => $finders,
	'exclude-namespaces' => [
		'IseardMedia\\Kudos\\',
		'Psr\\',
		'Composer'
	],
];
