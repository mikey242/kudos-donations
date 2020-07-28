<?php

use Kudos\Table\Transactions as Transactions_Table;

/**
 * Creates the transactions table
 *
 * @since    1.0.0
 */

$table = new Transactions_Table();
$table->prepare_items();
$message = '';

if ('delete' === $table->current_action()) {
	$message = __('Transaction deleted', 'kudos-donations');
} elseif ('bulk-delete' === $table->current_action() && isset($_REQUEST['bulk-action'])) {
	/* translators: %: Number of transactions */
	$message = sprintf(__('%s transaction(s) deleted', 'kudos-donations'), count($_REQUEST['bulk-action']));
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Transactions', 'kudos-donations'); ?></h1>
	<?php if (!empty($_REQUEST['s'])) { ?>
		<span class="subtitle">
                <?php
                /* translators: %s: Search term */
                printf(__('Search results for “%s”'), $_REQUEST['s'])
                ?>
            </span>
	<?php } ?>
	<p><?php _e("Your recent Kudos transactions",'kudos-donations');?></p>
	<?php if($message) { ?>
		<div class="updated below-h2" id="message"><p><?php echo esc_html($message); ?></p></div>
	<?php } ?>
	<form id="transactions-table" method="POST">
		<?php
		$table->display();
		?>
	</form>
</div>