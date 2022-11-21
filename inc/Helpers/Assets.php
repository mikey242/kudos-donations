<?php

namespace IseardMedia\Kudos\Helpers;

class Assets
{
    /**
     * Uses manifest to get asset URL.
     *
     * @param string $asset
     * @param bool $version
     *
     * @return string
     */
    public static function get_style(string $asset, bool $version = true): string
    {
        $suffix = 'dist/' . ltrim($asset, '/');
        $url    = KUDOS_PLUGIN_URL . $suffix;
        $path   = KUDOS_PLUGIN_DIR . $suffix;

        return $url . ($version ? '?ver=' . filemtime($path) : '');
    }

    /**
     * Returns an array with js file properties.
     * This includes checking for an accompanying .asset.php file.
     *
     * @param $asset
     * @param string $base_dir
     * @param string $base_url
     *
     * @return array|null
     */
    public static function get_script(
        $asset,
        string $base_dir = KUDOS_PLUGIN_DIR,
        string $base_url = KUDOS_PLUGIN_URL
    ): ?array {
        $asset_path = $base_dir . '/dist' . $asset;
        if (file_exists($asset_path)) {
            $out            = [];
            $out['path']    = $asset_path;
            $out['url']     = $base_url . 'dist/' . ltrim($asset, '/');
            $asset_manifest = substr_replace($asset_path, '.asset.php', -strlen('.js'));
            if (file_exists($asset_manifest)) {
                $manifest_content    = include($asset_manifest);
                $out['dependencies'] = $manifest_content['dependencies'] ?? [];
                $out['version']      = $manifest_content['version'] ?? KUDOS_VERSION;
            }

            return $out;
        }

        return null;
    }

    /**
     * Uses manifest to get asset path.
     *
     * @param string $asset
     *
     * @return string
     */
    public static function get_asset_path(string $asset): string
    {
        return KUDOS_PLUGIN_DIR . '/dist/' . ltrim($asset, '/');
    }
}
