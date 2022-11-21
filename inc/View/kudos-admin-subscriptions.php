<?php

use IseardMedia\Kudos\Service\AdminNotice;

/**
 * Creates the subscriptions table
 *
 * @since    1.1.0
 */

$table_action = $this->table->current_action();
$records      = isset($_REQUEST['bulk-action']) ? count($_REQUEST['bulk-action']) : 0;

switch ($table_action) {
    case 'cancel':
        $message = __('Subscription cancelled', 'kudos-donations');
        break;
    case 'delete':
        $message = __('Subscription deleted', 'kudos-donations');
        break;
    case 'bulk-delete':
        $message = sprintf(
        /* translators: %s: Number of records. */
            _n('Deleted %s subscription', 'Deleted %s subscriptions', $records, 'kudos-donations'),
            $records
        );
        break;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php
        esc_attr_e('Subscriptions', 'kudos-donations'); ?></h1>
    <?php
    if ( ! empty($_REQUEST['s'])) { ?>
		<span class="subtitle">
            <?php
            /* translators: %s: Search term */
            printf(__('Search results for “%s”'), $_REQUEST['s'])
            ?>
        </span>
        <?php
    } ?>
    <?php
    if (isset($message)) {
        $notice = new AdminNotice(esc_html($message));
        $notice->render();
    }
    ?>
	<form id="subscriptions-table" method="POST">
        <?php
        $this->table->display(); ?>
	</form>
</div>
