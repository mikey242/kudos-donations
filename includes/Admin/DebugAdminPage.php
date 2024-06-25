<?php
/**
 * Debug Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

class DebugAdminPage extends AbstractAdminPage implements HasCallbackInterface, HasAssetsInterface {
	private string $tab;
	private string $log_level;
	private string $log_file;

	/**
	 * Tools page constructor.
	 */
	public function __construct() {
		$this->tab       = $_GET['tab'] ?? 'log'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->log_level = $_GET['log_level'] ?? 'ALL'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->log_file  = KUDOS_STORAGE_DIR . 'logs/' . $_ENV['APP_ENV'] . '.log';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Debug', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Debug', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_slug(): string {
		return 'kudos-debug';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_position(): ?int {
		return 5;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_assets(): void {}

	/**
	 * Gets the log file as an array.
	 *
	 * @return array
	 */
	private function get_log(): array {
		$log_array = [];
		if ( file_exists( $this->log_file ) ) {
			$log_content = array_reverse( file( $this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) );
			$pattern     = '/\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.*[^ ]+) (?P<context>[^ ]+) (?P<extra>[^ ]+)/';
			foreach ( $log_content as $line ) {
				if ( preg_match( $pattern, $line, $log_matches ) ) {
					$log_array[] = $log_matches;
				}
			}

			if ( 'ALL' !== $this->log_level ) {
				$log_array = array_filter(
					$log_array,
					function ( $line ) {
						$levels = explode( '|', $this->log_level );
						return \in_array( $line[3], $levels, true );
					}
				);
			}
		}
		return $log_array;
	}

	/**
	 * {@inheritDoc}
	 */
	public function callback(): void {
		$url = '?page=' . $this->get_menu_slug();
		?>
		<div class="wrap">

			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_attr( add_query_arg( 'tab', 'log', $url ) ); ?>"
					class="nav-tab <?php echo ( 'log' === $this->tab ) ? 'nav-tab-active' : ''; ?>">Log</a>
				<?php
				if ( KUDOS_DEBUG ) :
					?>
					<a href="<?php echo esc_attr( add_query_arg( 'tab', 'actions', $url ) ); ?>"
						class="nav-tab <?php echo ( 'actions' === $this->tab ) ? 'nav-tab-active' : ''; ?>">Actions</a>
				<?php endif; ?>
			</nav>

			<div class="tab-content">

				<?php

				switch ( $this->tab ) :

					case 'log':
						$url = add_query_arg( 'tab', 'log', $url );
						?>

						<p>Current log file: <?php echo esc_textarea( $this->log_file ); ?></p>

						<a href="<?php echo esc_attr( add_query_arg( 'log_level', 'ALL', $url ) ); ?>" class="button-secondary">
							All
						</a>
						<a href="<?php echo esc_attr( add_query_arg( 'log_level', 'ERROR|WARNING', $url ) ); ?>" class="button-secondary">
							Error
						</a>
						<a href="<?php echo esc_attr( add_query_arg( 'log_level', 'DEBUG', $url ) ); ?>" class="button-secondary">
							Debug
						</a>


						<table class='form-table' style="table-layout: auto">
							<tbody>
							<tr>
								<th class='row-title'>Date</th>
								<th>Level</th>
								<th>Message</th>
								<th>Context</th>
							</tr>

							<?php
							foreach ( $this->get_log() as $key => $log ) {

								$level   = $log['level'];
								$style   = 'border-left-width: 10px; border-left-style: solid;';
								$message = $log['message'];
								$context = $log['context'];

								switch ( $level ) {
									case 'CRITICAL':
									case 'ERROR':
										$class = 'notice-error';
										break;
									case 'DEBUG':
										$class = 'notice-debug';
										break;
									default:
										$class = 'notice-' . strtolower( $level );
								}
								?>

								<tr style='<?php echo esc_attr( $style ); ?>'
									class='<?php echo esc_attr( ( 0 === $key % 2 ? 'alternate ' : null ) . $class ); ?>'>

									<td>
										<?php
										echo esc_textarea(
											wp_date(
												get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
												strtotime( $log['date'] )
											)
										);
										?>
									</td>
									<td><?php echo esc_attr( $level ); ?></td>
									<td><?php echo esc_textarea( $message ); ?></td>
									<td><?php echo '<code>' . esc_textarea( $context ) . '</code>'; ?></td>

								</tr>

							<?php } ?>

							</tbody>
						</table>

						<?php

						break;

					case 'actions':
						$url = add_query_arg( 'tab', 'actions', $url );
						?>
						<p><strong>Please use the following actions only if you are having issues. Remember to backup your data
								before
								performing any of these actions.</strong></p>
						<hr/>

						<p>Settings actions.</p>
						<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_mollie' ); ?>
							<button type='submit' class="button-secondary confirm" name='kudos_action'
									value='kudos_clear_mollie'>
								Reset Mollie settings
							</button>
						</form>

						<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_settings' ); ?>
							<button type='submit' class="button-secondary confirm" name='kudos_action' value='kudos_clear_settings'>
								Reset ALL settings
							</button>
						</form>

						<hr/>

						<p>Campaign actions.</p>
						<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_campaigns' ); ?>
							<button type='submit' class="button-secondary confirm" name='kudos_action'
									value='kudos_clear_campaigns'>
								Clear campaigns
							</button>
						</form>

						<hr/>

						<p>Cache actions.</p>
						<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_twig_cache' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_twig_cache'>Clear twig cache
							</button>
						</form>

						<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_container_cache' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_container_cache'>Clear container cache
							</button>
						</form>

						<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_log' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_log'>Clear log
							</button>
						</form>

						<?php do_action( 'kudos_debug_menu_actions_extra', $url ); ?>

						<?php
						break;

				endswitch;

				?>

			</div>

		</div>
		<?php
	}
}
