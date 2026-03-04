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
			  'friendsofphp/',
			  'laminas/',
			  'masterminds/',
			  'mollie/',
			  'monolog/',
			  'sabberworm/',
			  'symfony/config/',
			  'symfony/dependency-injection/',
			  'symfony/filesystem/',
			  'symfony/proxy-manager-bridge/',
			  'symfony/service-contracts/',
              'twig/'
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
		static function (string $filePath, string $prefix, string $content): string {
			// ResolveInstanceofConditionalsPass uses hardcoded offsets ('53', 44) based on the
			// unscoped class name length. Recompute them from the actual scoped class names.
			if (false === strpos($filePath, 'dependency-injection/Compiler/ResolveInstanceofConditionalsPass.php')) {
				return $content;
			}
			$def_len    = strlen($prefix . '\\Symfony\\Component\\DependencyInjection\\Definition');
			$child_len  = strlen($prefix . '\\Symfony\\Component\\DependencyInjection\\ChildDefinition');
			$insert_pos = 4 + strlen((string) $child_len) + $def_len - strlen('Definition');
			$content = str_replace("substr_replace(\$definition, '53', 2, 2)", "substr_replace(\$definition, '$child_len', 2, 2)", $content);
			return str_replace("substr_replace(\$definition, 'Child', 44, 0)", "substr_replace(\$definition, 'Child', $insert_pos, 0)", $content);
		},
		static function (string $filePath, string $prefix, string $content): string {
			// Rewrite use statements inside PhpDumper heredoc strings (not caught by php-scoper).
			if (false === strpos($filePath, 'dependency-injection/Dumper/PhpDumper.php')) {
				return $content;
			}
			return str_replace(
				'use Symfony\\Component\\DependencyInjection\\',
				'use ' . $prefix . '\\Symfony\\Component\\DependencyInjection\\',
				$content
			);
		},
		static function (string $filePath, string $prefix, string $content): string {
			// Rewrite ProxyManager type hint inside ProxyDumper heredoc string.
			if (false === strpos($filePath, 'proxy-manager-bridge/LazyProxy/PhpDumper/ProxyDumper.php')) {
				return $content;
			}
			return str_replace(
				'\\ProxyManager\\Proxy\\LazyLoadingInterface',
				'\\' . $prefix . '\\ProxyManager\\Proxy\\LazyLoadingInterface',
				$content
			);
		},
        static function (string $filePath, string $prefix, string $content): string {
            // Rewrite hardcoded Twig 'use' namespace strings written by ModuleNode into
            // compiled template cache files. PHP-Scoper cannot rewrite class names that
            // appear inside string literals, so these must be patched manually.
            if (false === strpos($filePath, 'twig/twig/src/Node/ModuleNode.php')) {
                return $content;
            }
            $escaped_prefix = str_replace('\\', '\\\\', $prefix);
            return str_replace(
                '"use Twig\\\\',
                '"use ' . $escaped_prefix . '\\\\Twig\\\\',
                $content
            );
        }
	],
];
