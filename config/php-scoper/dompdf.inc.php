<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for dompdf and its bundled dependencies:
 * dompdf/php-font-lib, dompdf/php-svg-lib, masterminds/html5.
 * sabberworm/php-css-parser is a separate runtime dep — see sabberworm.inc.php.
 */
return [
	'finders'  => [
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->path(
				[
					'dompdf/',
					'masterminds/',
				]
			)
			->in( 'vendor' ),
	],
	'patchers' => [

		/**
		 * Rewrite dynamic Dompdf class-name strings that php-scoper cannot detect.
		 * Dompdf builds FrameDecorator and FrameReflower class names at runtime via
		 * string concatenation inside factory methods.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			if (
				false === strpos( $filePath, 'dompdf/dompdf' )
				&& false === strpos( $filePath, 'masterminds/' )
			) {
				return $content;
			}

			$escaped = str_replace( '\\', '\\\\', $prefix );
			$content = str_replace(
				'"Dompdf\\\\FrameDecorator\\\\',
				'"' . $escaped . '\\\\Dompdf\\\\FrameDecorator\\\\',
				$content
			);
			$content = str_replace(
				'"Dompdf\\\\FrameReflower\\\\',
				'"' . $escaped . '\\\\Dompdf\\\\FrameReflower\\\\',
				$content
			);
			return str_replace(
				"'\\Dompdf\\Positioner\\\\'",
				"'\\" . $prefix . "\\Dompdf\\Positioner\\\\'",
				$content
			);
		},
	],
];
