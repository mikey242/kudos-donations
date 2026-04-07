<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Scoper config for all Symfony packages:
 *   symfony/config                  — FileLocator used by PhpFileLoader
 *   symfony/dependency-injection    — ContainerBuilder, PhpDumper, PhpFileLoader, ServiceLocator
 *   symfony/filesystem              — transitive dep of symfony/config
 *   symfony/proxy-manager-bridge   — ProxyDumper for lazy service generation
 *   symfony/service-contracts       — ServiceLocatorTrait, ResetInterface, etc.
 *
 * The full DI package is required because the container is compiled at runtime
 * (on the first request), so ContainerBuilder, all Compiler passes, and PhpDumper
 * are all needed — not just the generated Container at runtime.
 */
return [
	'finders'  => [
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->path(
				[
					'symfony/config/',
					'symfony/dependency-injection/',
					'symfony/filesystem/',
					'symfony/proxy-manager-bridge/',
					'symfony/service-contracts/',
				]
			)
			->in( 'vendor' ),
	],
	'patchers' => [

		/**
		 * Add `: bool` return type to `has()` for PSR-11 v2 compatibility.
		 * symfony/service-contracts v2.x omits the PHP return type declaration and
		 * only has a @return bool docblock. If another plugin loads psr/container v2
		 * (which declares `has(string $id): bool`) before ours, PHP throws a fatal
		 * error because our scoped implementation is missing the return type.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			$targets = [
				'service-contracts/ServiceLocatorTrait.php',
				'dependency-injection/ContainerInterface.php',
				'dependency-injection/Container.php',
				'dependency-injection/ContainerBuilder.php',
				'dependency-injection/ParameterBag/ContainerBag.php',
			];
			foreach ( $targets as $target ) {
				if ( false !== strpos( $filePath, $target ) ) {
					return preg_replace(
						'/public function has\(string \$\w+\)(?!:)/',
						'$0: bool',
						$content
					);
				}
			}
			return $content;
		},

		/**
		 * ResolveInstanceofConditionalsPass uses hardcoded offsets ('53', 44) based
		 * on the unscoped class name length. Recompute them from the actual scoped
		 * class names so the generated container string-replaces correctly.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			if ( false === strpos( $filePath, 'dependency-injection/Compiler/ResolveInstanceofConditionalsPass.php' ) ) {
				return $content;
			}
			$def_len    = strlen( $prefix . '\\Symfony\\Component\\DependencyInjection\\Definition' );
			$child_len  = strlen( $prefix . '\\Symfony\\Component\\DependencyInjection\\ChildDefinition' );
			$insert_pos = 4 + strlen( (string) $child_len ) + $def_len - strlen( 'Definition' );
			$content    = str_replace( "substr_replace(\$definition, '53', 2, 2)", "substr_replace(\$definition, '$child_len', 2, 2)", $content );
			return str_replace( "substr_replace(\$definition, 'Child', 44, 0)", "substr_replace(\$definition, 'Child', $insert_pos, 0)", $content );
		},

		/**
		 * Rewrite `use` statements inside PhpDumper heredoc strings (not caught by
		 * php-scoper). Also fixes the generated container's getParameter() return
		 * type for Symfony 7.x compatibility — PhpDumper's NOWDOC template omits
		 * it, causing a PHP fatal error.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			if ( false === strpos( $filePath, 'dependency-injection/Dumper/PhpDumper.php' ) ) {
				return $content;
			}
			$content = str_replace(
				'use Symfony\\Component\\DependencyInjection\\',
				'use ' . $prefix . '\\Symfony\\Component\\DependencyInjection\\',
				$content
			);
			return str_replace(
				'    public function getParameter(string $name)',
				'    public function getParameter(string $name): \UnitEnum|array|string|int|float|bool|null',
				$content
			);
		},

		/**
		 * Rewrite the ProxyManager type hint inside ProxyDumper's heredoc string.
		 */
		static function ( string $filePath, string $prefix, string $content ): string {
			if ( false === strpos( $filePath, 'proxy-manager-bridge/LazyProxy/PhpDumper/ProxyDumper.php' ) ) {
				return $content;
			}
			$escaped = str_replace( '\\', '\\\\', $prefix );
			return str_replace(
				'\\\\ProxyManager\\\\Proxy\\\\LazyLoadingInterface',
				'\\\\' . $escaped . '\\\\ProxyManager\\\\Proxy\\\\LazyLoadingInterface',
				$content
			);
		},
	],
];
