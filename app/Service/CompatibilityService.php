<?php

namespace IseardMedia\Kudos\Service;

class CompatibilityService
{
    /**
     * The plugin's required WordPress version
     *
     * @var string
     * @since 2.0.0
     */
    public $required_wp_version = '5.5';

    /**
     * The plugin's required PHP version
     *
     * @var string
     * @since 2.0.0
     */
    public $required_php_version = '7.2';

    /**
     * Holds any blocker error messages stopping plugin running
     *
     * @var array
     * @since 2.0.0
     */
    private $notices = [];

    /**
     * Check if dependencies are met and load plugin, otherwise display errors
     *
     * @return bool
     * @since 2.0.0
     */
    public function init(): bool
    {
        /* Check minimum requirements are met */
        $this->run_tests();

        /* Check if any errors were thrown, enqueue them and exit early */
        if (sizeof($this->notices) > 0) {
            $notice = $this->build_notice();
            new AdminNotice($notice['error'], 'error', $notice['details']);

            return false;
        }

        return true;
    }

    /**
     * Check if WordPress version is compatible
     *
     * @return bool Whether compatible or not
     * @since 2.0.0
     */
    public function check_wordpress_version(): bool
    {
        global $wp_version;

        /* WordPress version not compatible */
        if ( ! version_compare($wp_version, $this->required_wp_version, '>=')) {
            /* translators: %1$s: WordPress version number. */
            $this->notices[] = sprintf(
                esc_html__('WordPress Version %1$s is required.', 'kudos-donations'),
                $this->required_wp_version
            );

            return false;
        }

        return true;
    }

    /**
     * Check if PHP version is compatible
     *
     * @return bool Whether compatible or not
     * @since 2.0.0
     */
    public function check_php(): bool
    {
        /* Check PHP version is compatible */
        if ( ! version_compare(phpversion(), $this->required_php_version, '>=')) {
            /* translators:
                %1$s: Support URL.
                %2$s: Current PHP version.
                %3$s: Required PHP version.
            */
            $this->notices[] = sprintf(
                __(
                    'You are running an <a href="%1$s">outdated version of PHP</a> (%2$s). Kudos Donations requires at least PHP %3$s to work. Contact your web hosting provider to update.',
                    'kudos-donations'
                ),
                "https://wordpress.org/support/update-php/",
                phpversion(),
                $this->required_php_version
            );

            return false;
        }

        return true;
    }

    /**
     * Helper function to build the messages
     *
     * @return array
     *
     * @since 2.0.0
     */
    public function build_notice(): array
    {
        $notice['error']   = __('Kudos Donations Installation Problem', 'kudos-donations');
        $notice['details'] = "<p>" . __(
                'The minimum requirements for Kudos Donations have not been met. Please fix the issue(s) below to continue:',
                'kudos-donations'
            ) . "</p>";
        $notice['details'] .= "<ul style='padding-bottom: 0.5em'>";
        foreach ($this->notices as $error) :
            $notice['details'] .= "<li style='padding-left: 20px;list-style: inside'>" . $error . "</li>";
        endforeach;
        $notice['details'] .= "</ul>";

        return $notice;
    }

    /**
     * Add to notices array
     *
     * @param string $notice
     *
     * @since 2.0.0
     */
    public function add_notice(string $notice)
    {
        $this->notices[] = $notice;
    }

    /**
     * Run the specified tests
     *
     * @since 2.0.0
     */
    private function run_tests()
    {
        $this->check_wordpress_version();
        $this->check_php();
    }
}
