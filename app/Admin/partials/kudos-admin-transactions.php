<?php

use Kudos\Admin\Table\TransactionsTable;
use Kudos\Service\AdminNotice;

/**
 * Creates the transactions table
 *
 * @since    1.0.0
 */

$table = new TransactionsTable();
$table->prepare_items();
$table_action = $table->current_action();
$records      = isset( $_REQUEST['bulk-action'] ) ? count( $_REQUEST['bulk-action'] ) : 0;

switch ( $table_action ) {
	case 'delete':
		$message = __( 'Transaction deleted', 'kudos-donations' );
		break;
	case 'bulk-delete':
		$message = sprintf(
		/* translators: %s: Number of records */
			_n( 'Deleted %s transaction', 'Deleted %s transactions', $records, 'kudos-donations' ),
			$records
		);
		break;
}

echo get_search_query();

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Transactions', 'kudos-donations' ); ?></h1>
	<?php if ( isset( $_REQUEST['s'] ) ) { ?>
		<span class="subtitle">
			<?php
			/* translators: %s: Search term */
			printf( __( 'Search results for “%s”' ), $_REQUEST['s'] )
			?>
		</span>
	<?php } ?>
	<?php
	if ( isset( $message ) ) {
		$notice = new AdminNotice( esc_html( $message ) );
		$notice->render();
	}
	?>
	<form id="transactions-table" method="POST">
		<?php $table->display(); ?>
	</form>
</div>
