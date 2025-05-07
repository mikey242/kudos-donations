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
		      'monolog/'
	      ])
	      ->in('vendor'),
];

return [
	'prefix' => 'KudosDonationsDeps',
	'output-dir' => 'third-party',
	'finders' => $finders,
	'exclude-namespaces' => [
		'IseardMedia\\Kudos\\',
		'Psr\\',
		'Composer'
	],
];
