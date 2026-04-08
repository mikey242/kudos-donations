<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for twig/twig.
 *
 * The full package is included because Twig uses many Node and NodeVisitor classes
 * internally when compiling templates, making per-file enumeration fragile.
 */
return [
		'finders'  => [
			Finder::create()
				->files()
				->ignoreVCS( true )
				->ignoreDotFiles( true )
				->path( 'twig/' )
				->in( 'vendor' ),
		],
		'patchers' => [

			/**
			 * Rewrite hardcoded `use Twig\\` namespace strings written by ModuleNode
			 * into compiled template cache files. PHP-Scoper cannot rewrite class names
			 * that appear inside string literals, so these must be patched manually.
			 */
			static function ( string $filePath, string $prefix, string $content ): string {
				if ( false === strpos( $filePath, 'twig/twig/src/Node/ModuleNode.php' ) ) {
					return $content;
				}
				$escaped = str_replace( '\\', '\\\\', $prefix );
				return str_replace(
					'"use Twig\\\\',
					'"use ' . $escaped . '\\\\Twig\\\\',
					$content
				);
			},
		],
	];
