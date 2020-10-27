<?php

use Kudos\Entity\DonorEntity;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\MollieService;
use Mollie\Api\Resources\Subscription;

/**
 * Debug page render
 *
 * @since   2.0.0
 */

//Get the active tab from the $_GET param
$default_tab = 'log';
$tab         = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;

?>

<div class="wrap">

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=kudos-debug&tab=log"
           class="nav-tab <?php if ( $tab === 'log' ): ?>nav-tab-active<?php endif; ?>">Log</a>
        <a href="?page=kudos-debug&tab=actions"
           class="nav-tab <?php if ( $tab === 'actions' ): ?>nav-tab-active<?php endif; ?>">Actions</a>
        <a href="?page=kudos-debug&tab=subscriptions"
           class="nav-tab <?php if ( $tab === 'subscriptions' ): ?>nav-tab-active<?php endif; ?>">Subscriptions</a>
    </nav>

    <div class="tab-content">

		<?php

		$url            = admin_url( 'admin.php?page=kudos-debug' );

		switch ( $tab ):
			case 'actions':
				$url = add_query_arg( 'tab', 'actions', $url );
				?>
                <p>Please use the following actions only if you are having issues. Remember to backup your data before
                    performing any of these actions.</p>

                <hr/>

                <p>This will remove all the settings from the database and reset them back to default.</p>
                <form action="<?php echo $url ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_settings', '_wpnonce' ) ?>
                    <input type='hidden' name='kudos_action' value='kudos_clear_settings'>
                    <input class="button-secondary" type='submit' value='Clear settings'>
                </form>

                <hr/>

                <p>This will clear the twig cache.</p>
                <form action="<?php echo $url ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_cache', '_wpnonce' ) ?>
                    <input type='hidden' name='kudos_action' value='kudos_clear_cache'>
                    <input class="button-secondary" type='submit' value='Clear cache'>
                </form>

                <hr/>

                <p>Remove all transactions</p>
                <form action="<?php echo $url ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_transactions', '_wpnonce' ) ?>
                    <input type='hidden' name='kudos_action' value='kudos_clear_transactions'>
                    <input class="button-secondary" type='submit' value='Delete all transactions'>
                </form>

                <hr/>

                <p>Remove all donors</p>
                <form action="<?php echo $url ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_donors', '_wpnonce' ) ?>
                    <input type='hidden' name='kudos_action' value='kudos_clear_donors'>
                    <input class="button-secondary" type='submit' value='Delete all donors'>
                </form>

                <hr/>

                <p>Remove all subscriptions</p>
                <form action="<?php echo $url ?>" method='post'>
					<?php wp_nonce_field( 'kudos_clear_subscriptions', '_wpnonce' ) ?>
                    <input type='hidden' name='kudos_action' value='kudos_clear_subscriptions'>
                    <input class="button-secondary" type='submit' value='Delete all subscriptions'>
                </form>
				<?php
				break;

			case 'log':
				$url = add_query_arg( 'tab', 'log', $url );
				$file   = LoggerService::LOG_FILE;

				// Quit if file does not exist
				if ( ! file_exists( $file ) ) {
					return;
				}

				$kudos_logger = LoggerService::factory();
				$logArray     = $kudos_logger->get_as_array();
				?>

                <p>This logfile location: <?php echo $file ?></p>
                <p>Current filesize: <?php echo filesize( $file ) ?> bytes</p>

                <form style="display:inline-block;" action="<?php echo $url ?>"
                      method='post'>
					<?php wp_nonce_field( 'kudos_log_clear', '_wpnonce' ) ?>
                    <input type='hidden' name='kudos_action' value='kudos_log_clear'>
                    <input class="button-secondary" type='submit' value='Clear'>
                </form>

                <form style="display:inline-block;" action="<?php echo $url ?>"
                      method='post'>
					<?php wp_nonce_field( 'kudos_log_download', '_wpnonce' ) ?>
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
					foreach ( $logArray as $key => $log ) {

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

                        <tr style='<?php echo $style ?>'
                            class='<?php echo ( $key % 2 === 0 ? 'alternate ' : null ) . $class ?>'>

                            <td>
								<?php echo wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
									strtotime( $log['date'] ) ); ?>
                            </td>
                            <td>
								<?php echo( $log['type'] ); ?>
                            </td>
                            <td>
								<?php echo( $log['message'] ); ?>
                            </td>

                        </tr>

					<?php } ?>

                    </tbody>
                </table>

				<?php

				break;

			case 'subscriptions':
				$mapper = new MapperService( DonorEntity::class );
				$donors = $mapper->get_all_by();
				if ( $donors ) {
					$kudos_mollie = MollieService::factory();
					foreach ( $donors as $donor ) {

						$subscriptions = $kudos_mollie->get_subscriptions( $donor->customer_id );

						if ( ! count( $subscriptions ) ) {
							continue;
						}
						?>

                        <h3><strong><?php echo $donor->email ?></strong>
                            <span>(<?php echo $donor->customer_id ?>)</span></h3>
                        <form action="<?php echo admin_url( 'admin.php?page=kudos-debug&tab=subscriptions' ) ?>"
                              method='post'>
							<?php wp_nonce_field( 'kudos_cancel_subscription', '_wpnonce' ) ?>
                            <input type='hidden' name='kudos_action' value='kudos_cancel_subscription'>
                            <input type='hidden' name='customerId' value='<?php echo $donor->customer_id ?>'>

							<?php
							/** @var Subscription $subscription */
							foreach ( $subscriptions as $subscription ) {
								?>

                                <table class='widefat'>
                                    <tbody>

                                    <tr>
                                        <td class='row-title'>id</td>
                                        <td><?php echo $subscription->id ?></td>
                                    </tr>

                                    <tr class='alternate'>
                                        <td class='row-title'>status</td>
                                        <td>
											<?php echo $subscription->status ?>
											<?php if ( $subscription->status !== 'canceled' ) : ?>
                                            <button name='subscriptionId' type='submit'
                                                    value='<?php echo $subscription->id ?>'>Cancel
                                            </button>
                                        </td>
										<?php endif; ?>
                                    </tr>

                                    <tr>
                                        <td class='row-title'>amount</td>
                                        <td><?php echo $subscription->amount->value ?></td>
                                    </tr>

                                    <tr class='alternate'>
                                        <td class='row-title'>interval</td>
                                        <td><?php echo $subscription->interval ?></td>
                                    </tr>

                                    <tr>
                                        <td class='row-title'>times</td>
                                        <td><?php echo $subscription->times ?></td>
                                    </tr>

                                    <tr class='alternate'>
                                        <td class='row-title'>next payment</td>
                                        <td><?php echo( $subscription->nextPaymentDate ?? 'n/a' ) ?></td>
                                    </tr>

                                    <tr>
                                        <td class='row-title'>webhookUrl</td>
                                        <td><?php echo $subscription->webhookUrl ?></td>
                                    </tr>

                                    </tbody>
                                </table>
                                <br class='clear'>
							<?php } ?>
                        </form>
						<?php
					}
				}

				break;

		endswitch;

		?>

    </div>

</div>