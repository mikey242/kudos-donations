<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

$finders = [
	Finder::create()
	      ->files()
	      ->ignoreVCS(true)
	      ->ignoreDotFiles(true)
	      ->path([
			  'dompdf/',
			  'masterminds/',
			  'mollie/',
			  'monolog/',
			  'sabberworm/',
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
	'patchers' => [
		static function (string $filePath, string $prefix, string $content): string {
			// Rewrite legacy Mollie docblocks to match scoped namespace.
			$content = preg_replace_callback(
				'/@return\s+\\\\Mollie\\\\Api\\\\Resources\\\\([A-Za-z]+)/',
				static fn(array $matches): string => '@return \\' . $prefix . '\\Mollie\\Api\\Resources\\' . $matches[1],
				$content
			);

			// Rewrite dynamic Dompdf class name strings.
			$escaped_prefix = str_replace('\\', '\\\\', $prefix);
			$content = str_replace(
				'"Dompdf\\\\FrameDecorator\\\\',
				'"' . $escaped_prefix . '\\\\Dompdf\\\\FrameDecorator\\\\',
				$content
			);
			$content = str_replace(
				'"Dompdf\\\\FrameReflower\\\\',
				'"' . $escaped_prefix . '\\\\Dompdf\\\\FrameReflower\\\\',
				$content
			);
            return str_replace(
                "'\\Dompdf\\Positioner\\\\",
                "'\\" . $prefix . "\\Dompdf\\Positioner\\\\",
                $content
            );
		},
	],
];
