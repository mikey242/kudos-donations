<?php
/**
 * Tools view.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

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
		<a href="?page=kudos-tools&tab=log"
			class="nav-tab <?php echo ( 'log' === $tab ) ? 'nav-tab-active' : ''; ?>">Log</a>
		<?php
		if ( KUDOS_DEBUG ) :
			?>
			<a href="?page=kudos-tools&tab=actions"
				class="nav-tab <?php echo ( 'actions' === $tab ) ? 'nav-tab-active' : ''; ?>">Actions</a>
		<?php endif; ?>
	</nav>

	<div class="tab-content">

		<?php

		$url = admin_url( 'admin.php?page=kudos-tools' );

		switch ( $tab ) :

			case 'log':
				$url       = add_query_arg( 'tab', 'log', $url );
				$logger    = new LoggerService();
				$log_array = $logger->get_as_array();
				?>

				<p>Kudos Donations logs to the "<?php echo $logger->get_table_name(); ?>" table in the
					database.</p>

				<form style="display:inline-block;" action="<?php echo esc_url( $url ); ?>"
						method='post'>
					<?php wp_nonce_field( 'kudos_log_clear' ); ?>
					<button class="button-secondary confirm" name='kudos_action' type='submit' value='kudos_log_clear'>
						Clear
					</button>
				</form>

				<table class='form-table'>
					<tbody>
					<tr>
						<th class='row-title'>Date</th>
						<th>Level</th>
						<th>Message</th>
						<th>Context</th>
					</tr>

					<?php
					foreach ( $log_array as $key => $log ) {

						$level   = LoggerService::getLevelName( $log['level'] );
						$style   = 'border-left-width: 4px; border-left-style: solid;';
						$message = esc_textarea( $log['message'] );
						$context = esc_textarea( $log['context'] );

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
							<td>
								<?php echo esc_attr( $level ); ?>
							</td>
							<td title="<?php echo( $message ); ?>">
								<?php echo( Utils::truncate_string( $message, 255 ) ); ?>
							</td>
							<td title="<?php echo( $context ); ?>">
								<?php echo( Utils::truncate_string( $context, 255 ) ); ?>
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
				<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
					<?php wp_nonce_field( 'kudos_sync_transactions' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
							value='kudos_sync_transactions'>Sync transactions
					</button>
				</form>

				<form action="<?php echo esc_url( $url ); ?>" method='post' style="display: inline">
					<?php wp_nonce_field( 'kudos_add_missing_transactions' ); ?>
					<button class="button-secondary confirm" type='submit' name='kudos_action'
							value='kudos_add_missing_transactions'>Add missing transactions
					</button>
				</form>

				<?php do_action( 'kudos_debug_menu_actions_extra', $url ); ?>

				<?php
				break;

		endswitch;

		?>

	</div>

</div>
