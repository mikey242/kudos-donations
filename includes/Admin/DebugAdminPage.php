<?php
/**
 * Debug Admin Page.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;

class DebugAdminPage extends AbstractAdminPage implements HasCallbackInterface, SubmenuAdminPageInterface {
	private CampaignRepository $campaign_repository;
	private MigrationHandler $migration_handler;

	/**
	 * Tools page constructor.
	 *
	 * @param CampaignRepository $campaign_repository The campaign repository.
	 * @param MigrationHandler   $migration_handler   The migration handler.
	 */
	public function __construct( CampaignRepository $campaign_repository, MigrationHandler $migration_handler ) {
		$this->campaign_repository = $campaign_repository;
		$this->migration_handler   = $migration_handler;
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
	 * {@inheritDoc}
	 */
	public function callback(): void {
		?>
		<div class="wrap">

			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="tab-content">

				<?php
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

					<h2>Migration History</h2>
					<form method="post" action="">
						<?php wp_nonce_field( 'kudos_update_migration_history' ); ?>
						<table class="form-table">
							<?php
							$history = (array) get_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );
							foreach ( $this->migration_handler->get_migrations() as $migration ) {
								$version = $migration->get_version();
								printf(
									'<tr><th scope="row">%s</th><td><input type="checkbox" name="kudos_migration_history[]" value="%s"%s /></td></tr>',
									esc_html( $version ),
									esc_attr( $version ),
									\in_array( $version, $history, true ) ? ' checked' : ''
								);
							}
							?>
							<tr>
								<th scope="row"><label for="kudos_db_version"><?php esc_html_e( 'DB Version', 'kudos-donations' ); ?></label></th>
								<td><input type="text" id="kudos_db_version" name="kudos_db_version" value="<?php echo esc_attr( (string) get_option( MigrationHandler::SETTING_DB_VERSION ) ); ?>" class="regular-text" /></td>
							</tr>
						</table>
						<?php submit_button( __( 'Save', 'kudos-donations' ), 'secondary', 'kudos_action', false, [ 'data-confirm' => esc_attr__( 'Are you sure you want to update the migration history?', 'kudos-donations' ) ] ); ?>
						<input type="hidden" name="kudos_action" value="kudos_update_migration_history" />
					</form>
					<hr>
					<h2><?php esc_html_e( 'Log Files', 'kudos-donations' ); ?></h2>
					<?php
					$log_dir   = KUDOS_STORAGE_DIR . 'logs/';
					$log_files = glob( $log_dir . '*.log' );
					if ( ! empty( $log_files ) ) {
						echo '<ul>';
						foreach ( $log_files as $log_file ) {
							$filename = basename( $log_file );
							$url      = add_query_arg(
								[
									'file'     => $filename,
									'_wpnonce' => wp_create_nonce( 'wp_rest' ),
								],
								rest_url( 'kudos/v1/log/download' )
							);
							printf(
								'<li><a href="%s">%s</a> (%s)</li>',
								esc_url( $url ),
								esc_html( $filename ),
								esc_html( size_format( filesize( $log_file ) ) )
							);
						}
						echo '</ul>';
					} else {
						echo '<p>' . esc_html__( 'No log files found.', 'kudos-donations' ) . '</p>';
					}
					?>

					<hr/>

					<?php do_action( 'kudos_debug_menu_actions_extra' ); ?>

			</div>

		</div>
		<?php
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool {
		return KUDOS_DEBUG;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
