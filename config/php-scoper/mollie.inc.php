<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for mollie/mollie-api-php.
 *
 * The full directory is included because the Mollie client resolves endpoints and
 * resource classes dynamically at runtime, making per-file enumeration fragile.
 */
return [
	'finders'  => [
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->path( 'mollie/' )
			->in( 'vendor' ),
	],
	'patchers' => [

		/**
		 * Rewrite legacy Mollie @return docblocks that php-scoper cannot detect
		 * because they appear as plain strings inside PHPDoc comments.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			if ( false === strpos( $filePath, 'mollie/' ) ) {
				return $content;
			}

			return preg_replace_callback(
				'/@return\s+\\\\Mollie\\\\Api\\\\Resources\\\\([A-Za-z]+)/',
				static function ( array $matches ) use ( $prefix ): string {
					return '@return \\' . $prefix . '\\Mollie\\Api\\Resources\\' . $matches[1];
				},
				$content
			);
		},
	],
];
