<?php
/**
 * Debug Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Service\MigrationService;
use KudosDonationsDeps\Monolog\Handler\RotatingFileHandler;

class DebugAdminPage extends AbstractAdminPage implements HasCallbackInterface, SubmenuAdminPageInterface {

	private const LOG_DIR = KUDOS_STORAGE_DIR . 'logs/';

	/**
	 * Pattern used for parsing log entries.
	 *
	 * @source https://github.com/devdot/monolog-parser/blob/master/src/Parser.php
	 */
	private const PATTERN_MONOLOG2 =
		'/^' . // start with newline.
		'\[(?<datetime>.*)] ' . // find the date that is between two brackets [].
		'(?<channel>[\w-]+).(?<level>\w+): ' . // get the channel and log level, they look like this: channel.ERROR, follow by colon and space.
		"(?<message>[^\[{\\n]+)" . // next up is the message (containing anything except [ or {, nor a new line).
		'(?:(?<context> (\[.*?]|\{.*?}))|)' . // followed by a space and anything (non-greedy) in either square [] or curly {} brackets, or nothing at all (skips ahead to line end).
		'(?:(?<extra> (\[.*]|\{.*}))|)' . // followed by a space and anything (non-greedy) in either square [] or curly {} brackets, or nothing at all (skips ahead to line end).
		'\s{0,2}$/m';

	private ?array $log_files;
	private string $current_tab;
	private ?string $current_log_level;
	private ?string $current_log_file;

	/**
	 * Tools page constructor.
	 */
	public function __construct() {
		$this->current_tab       = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'log'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->log_files         = $this->get_logs();
		$log_file                = KUDOS_STORAGE_DIR . 'logs/' . KUDOS_APP_ENV . '-' . gmdate( RotatingFileHandler::FILE_PER_DAY ) . '.log';
		$this->current_log_file  = file_exists( $log_file ) ? $log_file : ( ! empty( $this->log_files ) ? end( $this->log_files ) : '' );
		$this->current_log_level = 'ALL';
		$this->process_form_data();
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
	public static function get_menu_slug(): string {
		return 'kudos-debug';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 100;
	}

	/**
	 * Gets the log file as an array.
	 */
	private function get_log_content(): array {
		$log_array = [];
		if ( file_exists( $this->current_log_file ) ) {
			$log_content = array_reverse( file( $this->current_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) );
			foreach ( $log_content as $line ) {
				if ( preg_match( self::PATTERN_MONOLOG2, $line, $log_matches ) ) {
					$log_array[] = $log_matches;
				}
			}

			if ( 'ALL' !== $this->current_log_level ) {
				$log_array = array_filter(
					$log_array,
					function ( $line ) {
						$levels = explode( '|', $this->current_log_level );
						return \in_array( $line['level'], $levels, true );
					}
				);
			}
		}
		return $log_array;
	}

	/**
	 * Gets an array of the log file paths.
	 */
	public static function get_logs(): ?array {
		return glob( self::LOG_DIR . '*.log' );
	}

	/**
	 * Gets the log file path to be displayed.
	 */
	private function process_form_data(): void {
		$log_option = isset( $_REQUEST['log_option'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['log_option'] ) ) : null;
		$log_level  = isset( $_REQUEST['log_level'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['log_level'] ) ) : null;
		if ( $log_option || $log_level ) {
			if ( isset( $_REQUEST['_wpnonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
				if ( wp_verify_nonce( $nonce, 'log' ) ) {
					$this->current_log_file  = $log_option;
					$this->current_log_level = $log_level;
				}
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function callback(): void {
		?>
		<div class="wrap">

			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_attr( add_query_arg( 'tab', 'log' ) ); ?>"
					class="nav-tab <?php echo ( 'log' === $this->current_tab ) ? 'nav-tab-active' : ''; ?>">Log</a>
				<?php
				if ( KUDOS_DEBUG ) :
					?>
					<a href="<?php echo esc_attr( add_query_arg( 'tab', 'actions' ) ); ?>"
						class="nav-tab <?php echo ( 'actions' === $this->current_tab ) ? 'nav-tab-active' : ''; ?>">Actions</a>
				<?php endif; ?>
			</nav>

			<div class="tab-content">

				<?php

				switch ( $this->current_tab ) :

					case 'log':
						if ( $this->log_files ) {
							?>
							<form name="log-form" action="" method='post' style="margin: 1em 0">
								<?php wp_nonce_field( 'log' ); ?>
									<label for="log_option"><?php echo esc_attr( __( 'Log file:', 'kudos-donations' ) ); ?></label>
									<select name="log_option" id="log_option" onChange="this.form.submit()">
										<?php
										foreach ( $this->log_files as $log ) {
											echo '<option ' . ( basename( $log ) === basename( $this->current_log_file ) ? 'selected' : '' ) . ' value="' . esc_attr( $log ) . '">' . esc_html( basename( $log ) ) . '</option>';
										}
										?>
									</select>
									<label for="log_level"><?php echo esc_attr( __( 'Log level:', 'kudos-donations' ) ); ?></label>
									<select name="log_level" id="log_level" onChange="this.form.submit()">
										<?php
										foreach ( [ 'ALL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG' ] as $level ) {
											echo '<option ' . ( $this->current_log_level === $level ? 'selected' : '' ) . ' value=' . esc_attr( $level ) . '>' . esc_html( $level ) . '</option>';
										}
										?>
									</select>
							</form>

						<table class='form-table' style="table-layout: auto">
							<tbody>
							<tr>
								<th class='row-title'>Date</th>
								<th>Level</th>
								<th>Message</th>
								<th>Context</th>
							</tr>

							<?php
							foreach ( $this->get_log_content() as $key => $log ) {

								$level   = $log['level'];
								$style   = 'border-left-width: 10px; border-left-style: solid;';
								$message = $log['message'];
								$context = $log['context'] ?? '[]';

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
												strtotime( $log['datetime'] )
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
						}
						break;

					case 'actions':
						?>
						<p><strong>Please use the following actions only if you are having issues. Remember to back up your data
								before
								performing any of these actions.</strong></p>
						<hr/>

						<h2>Settings actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_settings' ); ?>
							<button type='submit' class="button-secondary confirm" name='kudos_action' value='kudos_clear_settings'>
								Reset ALL settings
							</button>
						</form>

						<hr/>

						<h2>Campaign actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_campaigns' ); ?>
							<button type='submit' class="button-secondary confirm" name='kudos_action'
									value='kudos_clear_campaigns'>
								Clear campaigns
							</button>
						</form>

						<hr/>

						<h2>Cache actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_twig_cache' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_twig_cache'>Clear twig cache
							</button>
						</form>

						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_container_cache' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_container_cache'>Clear container cache
							</button>
						</form>

						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_all_cache' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_all_cache'>Clear all cache
							</button>
						</form>

						<hr/>

						<h2>Log actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_logs' ); ?>
							<button class="button-secondary confirm" type='submit' name='kudos_action'
									value='kudos_clear_logs'>Clear logs
							</button>
						</form>

						<hr/>

						<h2>Transaction actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_assign_orphan_transactions_to_campaign' ); ?>
							<label for="kudos_campaign"><?php esc_html_e( 'Assign unassigned transactions to campaign:', 'kudos-donations' ); ?></label><br/><select name="kudos_campaign" id="kudos_campaign">
								<?php
									$campaigns = CampaignPostType::get_posts();
								foreach ( $campaigns as $campaign ) {
									echo '<option value="' . esc_attr( $campaign->ID ) . '">' . esc_html( $campaign->post_title ) . '</option>';
								}
								?>
							</select>
							<button type='submit' class="button-secondary confirm" name='kudos_action' value='kudos_assign_orphan_transactions_to_campaign'>
								Assign
							</button>
						</form>

						<hr/>

						<h2>Migration History:</h2>
						<ul>
						<?php
						foreach ( get_option( MigrationService::SETTING_MIGRATION_HISTORY ) as $migration ) {
							echo '<li>' . esc_attr( $migration ) . '</li>';
						}
						?>
						</ul>

						<?php do_action( 'kudos_debug_menu_actions_extra' ); ?>

						<?php
						break;

				endswitch;

				?>

			</div>

		</div>
		<?php
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
