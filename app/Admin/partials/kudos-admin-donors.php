<?php

use Kudos\Admin\Table\DonorsTable;

/**
 * Creates the donors table
 *
 * @since    1.1.0
 */

$table = new DonorsTable();
$table->prepare_items();
$message = '';

if ('delete' === $table->current_action()) {
	$message = __('Donor deleted', 'kudos-donations');
} elseif ('bulk-cancel' === $table->current_action() && isset($_REQUEST['bulk-action'])) {
	/* translators: %s: Number of transactions */
	$message = sprintf(__('%s donors(s) deleted', 'kudos-donations'), count($_REQUEST['bulk-action']));
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Donors', 'kudos-donations'); ?></h1>
	<?php if (!empty($_REQUEST['s'])) { ?>
		<span class="subtitle">
                <?php
                /* translators: %s: Search term */
                printf(__('Search results for “%s”'), $_REQUEST['s'])
                ?>
            </span>
	<?php } ?>
	<?php if($message) { ?>
		<div class="updated below-h2" id="message"><p><?php echo esc_html($message); ?></p></div>
	<?php } ?>
	<form id="subscriptions-table" method="POST">
		<?php
		$table->display();
		?>
	</form>
</div>
