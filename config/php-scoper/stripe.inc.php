<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for stripe/stripe-php.
 */
return [
    'finders'  => [
        Finder::create()
            ->files()
            ->ignoreVCS( true )
            ->ignoreDotFiles( true )
            ->path( [ 'stripe/stripe-php' ] )
            ->in( 'vendor' ),
    ],
    'patchers' => [

		/**
		 * Rewrite \Stripe\ references in docblocks that php-scoper cannot detect
		 * because they appear as plain strings inside PHPDoc comments.
		 * Uses a negative lookbehind to avoid double-prefixing already-scoped
		 * references like \IseardMedia\Kudos\ThirdParty\Stripe\.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			if ( false === strpos( $filePath, 'stripe/stripe-php' ) ) {
				return $content;
			}

			return preg_replace_callback(
				'/(?<!ThirdParty)\\\\Stripe\\\\/',
				static function ( array $matches ) use ( $prefix ): string {
					return '\\' . $prefix . '\\Stripe\\';
				},
				$content
			);
		},
	],
];
