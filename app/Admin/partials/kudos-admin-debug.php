<?php

use Kudos\Service\LoggerService;

/**
 * Debug page render
 *
 * @since   2.0.0
 */

// Get the active tab from the $_GET param.
$default_tab = 'log';
$tab         = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;

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

		$url            = admin_url( 'admin.php?page=kudos-debug' );

		switch ( $tab ) :

			case 'log':
				$url = add_query_arg( 'tab', 'log', $url );
				$file   = LoggerService::LOG_FILE;

				// Quit if file does not exist.
				if ( ! file_exists( $file ) ) {
					return;
				}

				$kudos_logger = LoggerService::factory();
				$log_array    = $kudos_logger->get_as_array();
				?>

				<p>This logfile location: <?php echo esc_url( $file ); ?></p>
				<p>Current filesize: <?php echo esc_attr( filesize( $file ) ); ?> bytes</p>

				<form style="display:inline-block;" action="<?php echo esc_url( $url ); ?>"
				      method='post'>
					<?php wp_nonce_field( 'kudos_log_clear', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_log_clear'>
					<input class="button-secondary confirm" type='submit' value='Clear'>
				</form>

				<form style="display:inline-block;" action="<?php echo esc_url( $url ); ?>"
				      method='post'>
					<?php wp_nonce_field( 'kudos_log_download', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_log_download'>
					<input class="button-secondary" type='submit' value='Download'>
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
								$class = '';
								$style = '';
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

				<p>This will remove all the settings from the database and reset them back to default.</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_settings', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_clear_settings'>
					<input class="button-secondary confirm" type='submit' value='Clear settings'>
				</form>

				<hr/>

				<p>This will clear the twig cache.</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_cache', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_clear_cache'>
					<input class="button-secondary confirm" type='submit' value='Clear cache'>
				</form>

				<hr/>

				<p>Remove all transactions</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_transactions', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_clear_transactions'>
					<input class="button-secondary confirm" type='submit' value='Delete all transactions'>
				</form>

				<hr/>

				<p>Remove all donors</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_donors', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_clear_donors'>
					<input class="button-secondary confirm" type='submit' value='Delete all donors'>
				</form>

				<hr/>

				<p>Remove all subscriptions</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_subscriptions', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_clear_subscriptions'>
					<input class="button-secondary confirm" type='submit' value='Delete all subscriptions'>
				</form>

				<hr/>

				<p>This will <strong>delete all Kudos data</strong> and recreate the database</p>
				<form action="<?php echo esc_url( $url ); ?>" method='post'>
					<?php wp_nonce_field( 'kudos_recreate_database', '_wpnonce' ); ?>
					<input type='hidden' name='kudos_action' value='kudos_recreate_database'>
					<input class="button-secondary confirm" type='submit' value='Recreate database'>
				</form>


				<?php
				break;

		endswitch;

		?>

	</div>

</div>
