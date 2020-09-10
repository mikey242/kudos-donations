<?php

use Kudos\Admin\Table\SubscriptionsTable;
use Kudos\Service\AdminNotice;

/**
 * Creates the subscriptions table
 *
 * @since    1.1.0
 */

$table = new SubscriptionsTable();
$table->prepare_items();
$action = $table->current_action();

switch ($action) {
    case 'cancel':
	    $message = __('Subscription cancelled', 'kudos-donations');
	    break;
    case 'delete':
	    $message = __('Subscription deleted', 'kudos-donations');
	    break;
    case 'bulk-cancel':
	    $message = sprintf(__('%s subscription(s) cancelled', 'kudos-donations'), count($_REQUEST['bulk-action']));
	    break;
    case 'bulk-delete':
	    $message = sprintf(__('%s subscription(s) deleted', 'kudos-donations'), count($_REQUEST['bulk-action']));
	    break;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Subscriptions', 'kudos-donations'); ?></h1>
	<?php if (!empty($_REQUEST['s'])) { ?>
		<span class="subtitle">
                <?php
                /* translators: %s: Search term */
                printf(__('Search results for “%s”'), $_REQUEST['s'])
                ?>
            </span>
	<?php } ?>
	<?php if(isset($message)) {
        $notice = new AdminNotice(esc_html($message));
        $notice->render();
	} ?>
	<form id="subscriptions-table" method="POST">
		<?php
		$table->display();
		?>
	</form>
</div>