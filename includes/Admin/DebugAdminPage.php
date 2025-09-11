<?php
/**
 * Debug Admin Page.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\ThirdParty\Monolog\Handler\RotatingFileHandler;
use IseardMedia\Kudos\ThirdParty\Monolog\Logger;

// Ensure WordPress filesystem classes loaded.
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

class DebugAdminPage extends AbstractAdminPage implements HasCallbackInterface, SubmenuAdminPageInterface {

	private const LOG_DIR     = KUDOS_STORAGE_DIR . 'logs/';
	private const TAB_LOG     = 'log';
	private const TAB_ACTIONS = 'actions';


	private ?array $log_files;
	private string $current_tab;
	private string $current_log_level = 'ALL';
	private string $current_log_file;
	private CampaignRepository $campaign_repository;
	private \WP_Filesystem_Direct $file_system;

	/**
	 * Tools page constructor.
	 *
	 * @param CampaignRepository $campaign_repository The campaign repository.
	 */
	public function __construct( CampaignRepository $campaign_repository ) {
		$this->file_system         = new \WP_Filesystem_Direct( true );
		$this->campaign_repository = $campaign_repository;
		$this->current_tab         = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : self::TAB_ACTIONS; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->log_files           = $this->get_logs();
		$log_file                  = KUDOS_STORAGE_DIR . 'logs/' . KUDOS_APP_ENV . '-' . gmdate( RotatingFileHandler::FILE_PER_DAY ) . '.log';
		$this->current_log_file    = $this->file_system->exists( $log_file )
			? $log_file
			: ( \is_array( $this->log_files ) && [] !== $this->log_files ? end( $this->log_files ) : '' );
		$this->process_form_data();
		$this->add_js();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Advanced', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Advanced', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'kudos-advanced';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 100;
	}

	/**
	 * Add JS to the footer.
	 */
	private function add_js(): void {
		add_action(
			'admin_footer',
			function () {
				?>
			<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', function () {
					document.querySelectorAll('form button[data-confirm], form input[type=submit][data-confirm]').forEach(function (btn) {
						btn.addEventListener('click', function (e) {
							const message = btn.getAttribute('data-confirm');
							if (!confirm(message)) {
								e.preventDefault();
							}
						});
					});
				});
			</script>
				<?php
			}
		);
	}

	/**
	 * Gets the log file as an array.
	 */
	private function get_log_content(): array {
		$log_array = [];
		if ( file_exists( $this->current_log_file ) ) {
			$raw_content = $this->file_system->get_contents( $this->current_log_file );
			$lines       = array_filter( explode( "\n", $raw_content ) );

			foreach ( $lines as $line ) {
				$log_entry = json_decode( $line, true );
				if ( $log_entry && \is_array( $log_entry ) ) {
					// Convert JSON format to the expected format for the view.
					$log_matches = [
						'datetime' => $log_entry['datetime'] ?? '',
						'channel'  => $log_entry['channel'] ?? '',
						'level'    => $log_entry['level_name'] ?? '',
						'message'  => $log_entry['message'] ?? '',
						'context'  => ! empty( $log_entry['context'] ) ? wp_json_encode( $log_entry['context'] ) : '[]',
						'extra'    => ! empty( $log_entry['extra'] ) ? wp_json_encode( $log_entry['extra'] ) : '[]',
					];
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
		return array_reverse( $log_array );
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
		$log_option = isset( $_REQUEST['log_option'] ) && \is_string( $_REQUEST['log_option'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['log_option'] ) )
			: null;

		$log_level = isset( $_REQUEST['log_level'] ) && \is_string( $_REQUEST['log_level'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['log_level'] ) )
			: 'ALL';

		if ( null !== $log_option ) {
			if ( isset( $_REQUEST['_wpnonce'] ) && \is_string( $_REQUEST['_wpnonce'] ) ) {
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
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'actions' ) ); ?>"
					class="nav-tab <?php echo ( 'actions' === $this->current_tab ) ? 'nav-tab-active' : ''; ?>">Actions</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'log' ) ); ?>"
					class="nav-tab <?php echo ( 'log' === $this->current_tab ) ? 'nav-tab-active' : ''; ?>">Log</a>


			</nav>

			<div class="tab-content">

				<?php

				switch ( $this->current_tab ) :

					case self::TAB_ACTIONS:
						/** @var CampaignEntity[] $campaigns */
						$campaigns = $this->campaign_repository->all();
						?>

						<p><strong>Please use the following actions only if you are having issues. Remember to back up your data
								before
								performing any of these actions.</strong></p>
						<hr/>

						<h2>Settings actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_settings' ); ?>
							<?php submit_button( __( 'Reset ALL settings', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to reset all settings?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_clear_settings" />
						</form>

						<hr/>

						<h2>Campaign actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_campaigns' ); ?>
							<?php submit_button( __( 'Clear campaigns', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to delete all campaigns?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_clear_campaigns" />
						</form>

						<hr/>

						<h2>Cache actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_twig_cache' ); ?>
							<?php submit_button( __( 'Clear twig cache', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to clear twig cache?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_clear_twig_cache" />
						</form>

						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_container_cache' ); ?>
							<?php submit_button( __( 'Clear container cache', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to clear container cache?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_clear_container_cache" />
						</form>

						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_all_cache' ); ?>
							<?php submit_button( __( 'Clear all cache', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to clear all cache?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_clear_all_cache" />
						</form>

						<hr/>

						<h2>Log actions</h2>
						<form action="" method='post' style="display: inline">
							<?php wp_nonce_field( 'kudos_clear_logs' ); ?>
							<?php submit_button( __( 'Clear logs', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to clear the log?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_clear_logs" />
						</form>

						<hr/>

						<h2>Transaction actions</h2>

						<form method="post" action="">
							<?php wp_nonce_field( 'kudos_assign_transactions_to_campaign' ); ?>

							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="kudos_from_campaign"><?php esc_html_e( 'Transactions from:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<select name="kudos_from_campaign" id="kudos_from_campaign" class="regular-text">
											<optgroup label="<?php esc_attr_e( 'Global', 'kudos-donations' ); ?>">
												<option value="_all_transactions_"><?php esc_html_e( 'All transactions', 'kudos-donations' ); ?></option>
												<option value="_orphaned_transactions_"><?php esc_html_e( 'Orphaned transactions', 'kudos-donations' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Campaigns', 'kudos-donations' ); ?>">
												<?php
												foreach ( $campaigns as $campaign ) {
													printf(
														'<option value="%s">%s</option>',
														esc_attr( (string) $campaign->id ),
														esc_html( $campaign->title )
													);
												}
												?>
											</optgroup>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="kudos_to_campaign"><?php esc_html_e( 'Assign to campaign:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<select name="kudos_to_campaign" id="kudos_to_campaign" class="regular-text">
											<?php
											foreach ( $campaigns as $campaign ) {
												printf(
													'<option value="%s">%s</option>',
													esc_attr( (string) $campaign->id ),
													esc_html( $campaign->title )
												);
											}
											?>
										</select>
									</td>
								</tr>
							</table>

							<?php submit_button( __( 'Assign Transactions', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to move the selected transactions to the selected campaign?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_assign_transactions_to_campaign" />
						</form>

						<hr/>

						<h2>Relink entities</h2>

						<form method="post" action="">
							<?php wp_nonce_field( 'kudos_link_entities' ); ?>

							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="kudos_source_repo"><?php esc_html_e( 'Source repo:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<select name="kudos_source_repo" id="kudos_source_repo" class="regular-text">
											<option value="<?php echo DonorRepository::class; ?>"><?php esc_html_e( 'Donors', 'kudos-donations' ); ?></option>
											<option value="<?php echo CampaignRepository::class; ?>"><?php esc_html_e( 'Campaigns', 'kudos-donations' ); ?></option>
											<option value="<?php echo TransactionRepository::class; ?>"><?php esc_html_e( 'Transactions', 'kudos-donations' ); ?></option>
											<option value="<?php echo SubscriptionRepository::class; ?>"><?php esc_html_e( 'Subscriptions', 'kudos-donations' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="kudos_local_key"><?php esc_html_e( 'Local key:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<input name="kudos_local_key" id="kudos_local_key" class="regular-text" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="kudos_vendor_key"><?php esc_html_e( 'Vendor key:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<input name="kudos_vendor_key" id="kudos_vendor_key" class="regular-text" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="kudos_target_repo"><?php esc_html_e( 'Target repo:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<select name="kudos_target_repo" id="kudos_target_repo" class="regular-text">
											<option value="<?php echo DonorRepository::class; ?>"><?php esc_html_e( 'Donors', 'kudos-donations' ); ?></option>
											<option value="<?php echo CampaignRepository::class; ?>"><?php esc_html_e( 'Campaigns', 'kudos-donations' ); ?></option>
											<option value="<?php echo TransactionRepository::class; ?>"><?php esc_html_e( 'Transactions', 'kudos-donations' ); ?></option>
											<option value="<?php echo SubscriptionRepository::class; ?>"><?php esc_html_e( 'Subscriptions', 'kudos-donations' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="kudos_target_vendor_key"><?php esc_html_e( 'Target vendor key:', 'kudos-donations' ); ?></label>
									</th>
									<td>
										<input name="kudos_target_vendor_key" id="kudos_target_vendor_key" class="regular-text" />
									</td>
								</tr>
							</table>

							<?php submit_button( __( 'Assign Transactions', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to move the selected transactions to the selected campaign?', 'kudos-donations' ) ] ); ?>
							<input type="hidden" name="kudos_action" value="kudos_link_entities" />
						</form>

						<hr/>

						<h2>Migration History:</h2>
						<ul>
							<?php
							foreach ( get_option( MigrationHandler::SETTING_MIGRATION_HISTORY ) as $migration ) {
								echo '<li>' . esc_attr( $migration ) . '</li>';
							}
							?>
						</ul>

						<?php do_action( 'kudos_debug_menu_actions_extra' ); ?>

						<?php
						break;

					case self::TAB_LOG:
						if ( null !== $this->log_files ) {
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
							<thead>
							<tr>
								<th><?php esc_html_e( 'Date', 'kudos-donations' ); ?></th>
								<th><?php esc_html_e( 'Level', 'kudos-donations' ); ?></th>
								<th><?php esc_html_e( 'Message', 'kudos-donations' ); ?></th>
								<th><?php esc_html_e( 'Context', 'kudos-donations' ); ?></th>
							</tr>
							</thead>

							<tbody>

							<?php
							foreach ( $this->get_log_content() as $key => $log ) {

								$level   = $log['level'];
								$style   = 'border-left-width: 10px; border-left-style: solid;';
								$message = $log['message'];
								$context = $log['context'] ?? '[]';

								switch ( $level ) {
									case Logger::CRITICAL:
									case Logger::ERROR:
										$class = 'notice-error';
										break;
									case Logger::DEBUG:
										$class = 'notice-debug';
										break;
									default:
										$class = 'notice-' . strtolower( $level );
								}
								?>

								<tr style='<?php echo esc_attr( $style ); ?>'
									class='<?php echo esc_attr( ( 0 === (int) $key % 2 ? 'alternate ' : '' ) . $class ); ?>'>

									<td>
										<?php
										$date_format = ( get_option( 'date_format' ) ?? 'Y-m-d' ) . ' ' . ( get_option( 'time_format' ) ?? 'H:i:s' );
										$date        = wp_date(
											$date_format,
											strtotime( $log['datetime'] )
										);
										if ( false !== $date ) {
											echo esc_textarea( $date );
										}
										?>
									</td>
									<td><code><?php echo esc_attr( $level ); ?></code></td>
									<td><?php echo esc_textarea( $message ); ?></td>
									<td><code><?php echo esc_textarea( $context ); ?></code></td>

								</tr>

							<?php } ?>

							</tbody>
						</table>

							<?php
						}
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
