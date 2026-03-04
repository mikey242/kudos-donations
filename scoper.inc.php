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

        /**
         * Add `: bool` return type to `has()` for PSR-11 v2 compatibility.
         * symfony/service-contracts v2.x omits the PHP return type declaration as only has
         * a @return bool docblock. If another plugin loads psr/container v2 which
         * declares `has(string $id): bool` before ours, PHP throws a fatal error because
         * our scoped implementation is missing the return type.
         */
        static function (string $filePath, string $prefix, string $content): string {
            $targets = [
                'service-contracts/ServiceLocatorTrait.php',
                'dependency-injection/ContainerInterface.php',
                'dependency-injection/Container.php',
                'dependency-injection/ContainerBuilder.php',
                'dependency-injection/ParameterBag/ContainerBag.php',
            ];
            foreach ($targets as $target) {
                if (false !== strpos($filePath, $target)) {
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
         * Rewrite legacy Mollie docblocks and dynamic Dompdf class name strings.
         * Runs on all files since these references are scattered across vendor code.
         */
        static function (string $filePath, string $prefix, string $content): string {
            $content = preg_replace_callback(
                '/@return\s+\\\\Mollie\\\\Api\\\\Resources\\\\([A-Za-z]+)/',
                static fn(array $matches): string => '@return \\' . $prefix . '\\Mollie\\Api\\Resources\\' . $matches[1],
                $content
            );

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

        /**
         * ResolveInstanceofConditionalsPass uses hardcoded offsets ('53', 44) based on the
         * unscoped class name length. Recompute them from the actual scoped class names.
         */
        static function (string $filePath, string $prefix, string $content): string {
            if (false === strpos($filePath, 'dependency-injection/Compiler/ResolveInstanceofConditionalsPass.php')) {
                return $content;
            }
            $def_len    = strlen($prefix . '\\Symfony\\Component\\DependencyInjection\\Definition');
            $child_len  = strlen($prefix . '\\Symfony\\Component\\DependencyInjection\\ChildDefinition');
            $insert_pos = 4 + strlen((string) $child_len) + $def_len - strlen('Definition');
            $content = str_replace("substr_replace(\$definition, '53', 2, 2)", "substr_replace(\$definition, '$child_len', 2, 2)", $content);
            return str_replace("substr_replace(\$definition, 'Child', 44, 0)", "substr_replace(\$definition, 'Child', $insert_pos, 0)", $content);
        },

        /**
         * Rewrite use statements inside PhpDumper heredoc strings( not caught by php - scoper).
         * Also fixes the generated container's getParameter() return type for Symfony 7.x
         * compatibility — PhpDumper's NOWDOC template omits it, causing a PHP fatal error.
         */
        static function (string $filePath, string $prefix, string $content): string {
            if (false === strpos($filePath, 'dependency-injection/Dumper/PhpDumper.php')) {
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
         * Rewrite ProxyManager type hint inside ProxyDumper heredoc string.
         */
        static function (string $filePath, string $prefix, string $content): string {
            if (false === strpos($filePath, 'proxy-manager-bridge/LazyProxy/PhpDumper/ProxyDumper.php')) {
                return $content;
            }
            return str_replace(
                '\\ProxyManager\\Proxy\\LazyLoadingInterface',
                '\\' . $prefix . '\\ProxyManager\\Proxy\\LazyLoadingInterface',
                $content
            );
        },

        /**
         * Rewrite hardcoded Twig 'use' namespace strings written by ModuleNode into
         * compiled template cache files. PHP-Scoper cannot rewrite class names that
         * appear inside string literals, so these must be patched manually.
         */
        static function (string $filePath, string $prefix, string $content): string {
            if (false === strpos($filePath, 'twig/twig/src/Node/ModuleNode.php')) {
                return $content;
            }
            $escaped_prefix = str_replace('\\', '\\\\', $prefix);
            return str_replace(
                '"use Twig\\\\',
                '"use ' . $escaped_prefix . '\\\\Twig\\\\',
                $content
            );
        },
    ],
];
