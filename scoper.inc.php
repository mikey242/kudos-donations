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
	      ])
	      ->in('vendor'),
];

return [
	'prefix' => 'IseardMedia\\Kudos\\ThirdParty',
	'finders' => $finders,
	'exclude-namespaces' => [
		'IseardMedia\\Kudos\\',
		'Psr\\',
		'Composer',
		'Masterminds'
	],
	'patchers' => [
		static function (string $filePath, string $prefix, string $content): string {
			// Rewrite legacy Mollie docblocks to match scoped namespace
			return preg_replace_callback(
				'/@return\s+\\\\Mollie\\\\Api\\\\Resources\\\\([A-Za-z]+)/',
				static fn(array $matches): string => '@return \\' . $prefix . '\\Mollie\\Api\\Resources\\' . $matches[1],
				$content
			);
		},
	],
];
