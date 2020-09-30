<?php

namespace Kudos\Service;

class CompatibilityService {

	/**
	 * The plugin's required WordPress version
	 *
	 * @var string
	 * @since 2.0.0
	 */
	public $required_wp_version = '5.4';

	/**
	 * The plugin's required PHP version
	 *
	 * @var string
	 * @since 2.0.0
	 */
	public $required_php_version = '7.1';

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
	public function init() {

		/* Check minimum requirements are met */
		$this->run_tests();

		/* Check if any errors were thrown, enqueue them and exit early */
		if ( sizeof( $this->notices ) > 0 ) {
			$notice = $this->build_notice();
			new AdminNotice( $notice['error'], 'error', $notice['details'] );

			return false;
		}

		return true;

	}

	/**
	 * Run the specified tests
	 *
	 * @since 2.0.0
	 */
	private function run_tests() {

		$this->check_wordpress_version();
		$this->check_php();

	}

	/**
	 * Check if WordPress version is compatible
	 *
	 * @return boolean Whether compatible or not
	 * @since 2.0.0
	 */
	public function check_wordpress_version() {

		global $wp_version;

		/* WordPress version not compatible */
		if ( ! version_compare( $wp_version, $this->required_wp_version, '>=' ) ) {
			/* translators: %1$s: WordPress version number. */
			$this->notices[] = sprintf( esc_html__( 'WordPress Version %1$s is required.', 'kudos-donations' ),
				$this->required_wp_version );

			return false;
		}

		return true;

	}

	/**
	 * Check if PHP version is compatible
	 *
	 * @return boolean Whether compatible or not
	 * @since 2.0.0
	 */
	public function check_php() {

		/* Check PHP version is compatible */
		if ( ! version_compare( phpversion(), $this->required_php_version, '>=' ) ) {
			/* translators:
				%1$s: Opening link tag.
				%2$s: Closing link tag.
				%3$s: Current PHP version.
				%4$s: Required PHP version.
			*/
			$this->notices[] = sprintf( esc_html__( 'You are running an %1$soutdated version of PHP%2$s (%3$s). Kudos Donations requires at least PHP %4$s to work. Contact your web hosting provider to update.',
				'kudos-donations' ),
				'<a href="https://wordpress.org/support/update-php/">',
				'</a>',
				phpversion(),
				$this->required_php_version );

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
	public function build_notice() {

		$notice['error']   = __( 'Kudos Donations Installation Problem', 'kudos-donations' );
		$notice['details'] = "<p>" . __( 'The minimum requirements for Kudos Donations have not been met. Please fix the issue(s) below to continue:',
				'kudos-donations' ) . "</p>";
		$notice['details'] .= "<ul style='padding-bottom: 0.5em'>";
		foreach ( $this->notices as $error ):
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
	public function add_notice( string $notice ) {

		$this->notices[] = $notice;

	}

}