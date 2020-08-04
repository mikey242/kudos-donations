<?php

use Kudos\Entity\Donor;
use Kudos\Service\Logger;
use Kudos\Service\Mapper;
use Kudos\Service\Mollie;
use Mollie\Api\Resources\Subscription;

/**
 * Debug page render
 *
 * @since   2.0.0
 */

$kudos_donor = new Donor();
$kudos_mollie = new Mollie();

//Get the active tab from the $_GET param
$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
?>

<div class="wrap">

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=kudos-debug" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Log</a>
        <a href="?page=kudos-debug&tab=subscriptions" class="nav-tab <?php if($tab==='subscriptions'):?>nav-tab-active<?php endif; ?>">Subscriptions</a>
    </nav>

    <div class="tab-content">

        <?php
        switch($tab):
        case 'subscriptions':
            $mapper = new Mapper(Donor::class);
            $donors = $mapper->get_all_by();
            if($donors) {
                foreach($donors as $donor) {

                    $subscriptions = $kudos_mollie->get_subscriptions($donor->customer_id);

                    if(!count($subscriptions)) {
                        continue;
                    }
                    ?>

                    <h3><strong><?php echo $donor->email ?></strong> <span>(<?php echo $donor->customer_id ?>)</span></h3>
                    <form action="<?php echo admin_url( 'admin-post.php?page=kudos-debug&tab=subscriptions' ) ?>" method='post'>
                        <?php wp_nonce_field('cancel_subscription', '_wpnonce') ?>
                        <input type='hidden' name='action' value='cancel_subscription'>
                        <input type='hidden' name='customerId' value='<?php echo $donor->customer_id ?>'>

                        <?php
                        /** @var Subscription $subscription */
                        foreach ($subscriptions as $subscription) {
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
                                        <?php if($subscription->status !== 'canceled') :?>
                                            <button name='subscriptionId' type='submit' value='<?php echo $subscription->id ?>'>Cancel</button></td>
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
                                    <td><?php echo ($subscription->nextPaymentDate ?? 'n/a') ?></td>
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
        default:
            $file = Logger::LOG_FILE;
            $kudos_logger = new Logger();
            $logArray = $kudos_logger->getAsArray();
            $download_nonce = wp_create_nonce('download-' . basename($file));
            $clear_nonce = wp_create_nonce('clear-' . basename($file));
            ?>

            <p>This logfile location: <?php echo $file ?></p>
            <p>Current filesize: <?php echo filesize($file) ?> bytes</p>
            <a href="/wp-admin/admin.php?page=kudos-debug&_wpnonce=<?php echo $clear_nonce ?>&clear_log" class="button action">Clear</a>
            <a href="/wp-admin/admin.php?page=kudos-debug&_wpnonce=<?php echo $download_nonce ?>&download_log" class="button action">Download</a>
            <table class='form-table'><tbody>
                <tr>
                    <th class='row-title'>Date</th>
                    <th>Level</th>
                    <th>Message</th>
                </tr>

                <?php
                foreach ($logArray as $key=>$log) {

                    $level = $log['type'];
                    $style = 'border-left-width: 4px; border-left-style: solid;';

                    switch ($level) {
                        case 'CRITICAL':
                        case 'ERROR':
                            $class = 'notice-error';
                            break;
                        case 'DEBUG':
                            $class='';
                            $style='';
                            break;
                        default:
                            $class = 'notice-' . strtolower( $level );
                    }
                    ?>

                    <tr style='<?php echo $style ?>' class='<?php echo ($key %2===0 ? 'alternate ' : null) . $class ?>'>

                    <td>
                    <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['date'])); ?>
                    </td>
                    <td>
                    <?php echo($log['type']); ?>
                    </td>
                    <td>
                    <?php echo($log['message']); ?>
                    </td>

                    </tr>

                <?php } ?>

            </tbody></table>

        <?php endswitch; ?>

    </div>

</div>