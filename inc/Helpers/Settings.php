<?php

namespace IseardMedia\Kudos\Helpers;

use IseardMedia\Kudos\Controller\Admin;

class Settings
{
    public const PREFIX = '_kudos_';

    /**
     * Gets the settings for the current vendor.
     *
     * @return mixed
     */
    public static function get_current_vendor_settings(): mixed {
        return self::get_setting('vendor_' . self::get_setting('vendor'));
    }

	/**
	 * Returns setting value.
	 *
	 * @param string $name Setting name without prefix.
	 *
	 * @return mixed
	 */
    public static function get_setting(string $name): mixed {
        return get_option(self::PREFIX . $name);
    }

    /**
     * Updates specific values in serialized settings array.
     * e.g. update_array('my_setting', ['enabled' => false]).
     *
     * @param string $name Setting array name without prefix.
     * @param array $value Array of name=>values in setting to update.
     * @param bool $unique Whether the resulting array should have unique values.
     *
     * @return bool
     */
    public static function update_array(string $name, array $value, bool $unique = false): bool
    {
        // Grab current data.
        $current = self::get_setting($name);

        // Check if setting is either an array or null.
        if (is_array($current) || ! null) {
            // Merge provided data and current data then update setting.
            $new = wp_parse_args($value, $current);

            return self::update_setting($name, $unique ? array_unique($new) : $new);
        }

        return false;
    }

    /**
     * Update specified setting.
     *
     * @param string $name Setting name without prefix.
     * @param mixed $value Setting value.
     *
     * @return bool
     */
    public static function update_setting(string $name, $value): bool
    {
        return update_option(self::PREFIX . $name, $value);
    }

    /**
     * Removes all settings from database.
     */
    public static function remove_settings(array $settings = [])
    {
        foreach ($settings as $key => $setting) {
            self::remove_setting($key);
        }
    }

    /**
     * Remove specified setting from database.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function remove_setting(string $name): bool
    {
        return delete_option(self::PREFIX . $name);
    }
}
