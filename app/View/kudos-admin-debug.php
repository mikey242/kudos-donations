<?php

use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;

/**
 * Debug page render.
 */

// Get the active tab from the $_GET param.
$default_tab = 'log';
$tab         = $_GET['tab'] ?? $default_tab;

?>

<div class="wrap">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?page=kudos-debug&tab=log"
		   class="nav-tab <?php echo ( 'log' === $tab ) ? 'nav-tab-active' : ''; ?>">Log</a>
		<a href="?page=kudos-debug&tab=actions"
		   class="nav-tab <?php echo ( 'actions' === $tab ) ? 'nav-tab-active' : ''; ?>">Actions</a>
	</nav>

	<div class="tab-content">

		<?php

		$url          = admin_url( 'admin.php?page=kudos-debug' );

		switch ( $tab ) :

			case 'log':
				$url = add_query_arg( 'tab', 'log', $url );
				$file = LoggerService::LOG_FILE;

				// Quit if file does not exist.
				if ( ! file_exists( $file ) ) {
					return;
				}

				$log_array = LoggerService::get_as_array();
				?>

				<p>This logfile location: <?php echo esc_url( $file ); ?></p>
				<p>Current filesize: <?php echo Utils::human_filesize( (int) filesize( $file ) ); ?></p>
				<p><strong>Note: The log will be automatically cleared when it reaches 2MB.</strong></p>

				<form style="display:inline-block;" action="<?php echo esc_url( $url ); ?>"
				      method='post'>
					<?php wp_nonce_field( 'kudos_log_clear' ); ?>
					<button class="button-secondary confirm" name='kudos_action' type='submit' value='kudos_log_clear'>
						Clear
					</button>
				</form>
				<form style="display:inline-block;" action="<?php echo esc_url( $url ); ?>"
				      method='post'>
					<?php wp_nonce_field( 'kudos_log_download' ); ?>
					<button class="button-secondary" name='kudos_action' type='submit' value='kudos_log_download'>
						Download
					</button>
				</form>

				<table class='form-table'>
					<tbody>
					<tr>
						<th class='row-title'>Date</th>
						<th>Level</th>
						<th>Message</th>
					</tr>

					<?php
					foreach ( $log_array as $key => $log ) {

						$level = $log['type'];
						$style = 'border-left-width: 4px; border-left-style: solid;';

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
									wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
										strtotime( $log['date'] ) )
								);
								?>
							</td>
							<td>
								<?php echo esc_attr( $log['type'] ); ?>
							</td>
							<td>
								<?php echo( esc_textarea( $log['message'] ) ); ?>
							</td>

						</tr>

					<?php } ?>

					</tbody>
				</table>

				<?php

				break;

			case 'actions':
				$url = add_query_arg( 'tab', 'actions', $url );
				?>
				<p>Please use the following actions only if you are having issues. Remember to backup your data before
					performing any of these actions.</p>
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
					<?php wp_nonce_field( 'kudos_clear_campaigns' ); ?>
					<button type='submit' class="button-secondary confirm" name='kudos_action'
					        value='kudos_clear_campaigns'>
						Reset campaigns settings
					</button>
				</form>

				<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
					<?php wp_nonce_field( 'kudos_clear_all' ); ?>
					<button type='submit' class="button-secondary confirm" name='kudos_action' value='kudos_clear_all'>
						Reset all settings
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
					<?php wp_nonce_field( 'kudos_clear_object_cache' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
					        value='kudos_clear_object_cache'>Clear object cache
					</button>
				</form>

				<hr/>

				<p>Table actions.</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
					<?php wp_nonce_field( 'kudos_clear_transactions' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
					        value='kudos_clear_transactions'>Delete all
						transactions
					</button>
				</form>

				<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
					<?php wp_nonce_field( 'kudos_clear_donors' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
					        value='kudos_clear_donors'>Delete all
						donors
					</button>
				</form>

				<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
					<?php wp_nonce_field( 'kudos_clear_subscriptions' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
					        value='kudos_clear_subscriptions'>Delete all
						subscriptions
					</button>
				</form>

				<hr/>

				<p>This will <strong>delete all Kudos data</strong> and recreate the database.</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_recreate_database' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
					        value='kudos_recreate_database'>Recreate
						database
					</button>
				</form>

				<hr/>

				<p>Mollie actions.</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_sync_payments' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
					        value='kudos_sync_payments'>Sync payments
					</button>
				</form>

				<?php do_action( 'kudos_debug_menu_actions_extra', $url ); ?>

				<?php
				break;

		endswitch;

		?>

	</div>

</div>