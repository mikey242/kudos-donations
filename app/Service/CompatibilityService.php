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
			add_action( 'admin_notices', [ $this, 'display_notices' ] );

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
     * Add to notices array
     *
	 * @param $notice
     * @since 2.0.0
	 */
	public function add_notice($notice) {

	    $this->notices[] = $notice;

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
			$this->notices[] = sprintf( esc_html__( 'WordPress Version %1$s is required.', 'kudos-donations' ), $this->required_wp_version );

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
			$this->notices[] = sprintf( esc_html__( 'You are running an %1$soutdated version of PHP%2$s (%3$s). Kudos Donations requires at least PHP %4$s to work. Contact your web hosting provider to update.', 'kudos-donations' ), '<a href="https://wordpress.org/support/update-php/">', '</a>', phpversion(), $this->required_php_version);

			return false;
		}

		return true;
	}


	/**
	 * Helper function to easily display error messages
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public function display_notices() {
		?>
		<div class="error">
			<p><strong><?php esc_html_e( 'Kudos Donations Installation Problem', 'kudos-donations' ); ?></strong></p>

			<p><?php esc_html_e( 'The minimum requirements for Kudos Donations have not been met. Please fix the issue(s) below to continue:', 'kudos-donations' ); ?></p>
			<ul style="padding-bottom: 0.5em">
				<?php foreach ( $this->notices as $notice ): ?>
					<li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}


}