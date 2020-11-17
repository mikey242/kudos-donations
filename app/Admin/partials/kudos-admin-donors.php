<?php

use Kudos\Admin\Table\DonorsTable;
use Kudos\Service\AdminNotice;

/**
 * Creates the donors table
 *
 * @since    1.1.0
 */

$table = new DonorsTable();
$table->prepare_items();
$table_action = $table->current_action();
$records      = isset( $_REQUEST['bulk-action'] ) ? count( $_REQUEST['bulk-action'] ) : 0;

switch ( $table_action ) {
	case 'delete':
		$message = __( 'Donor deleted', 'kudos-donations' );
		break;
	case 'bulk-delete':
		/* translators: %s: Number of records */
		$message = sprintf( _n( 'Deleted %s donor', 'Deleted %s donors', $records, 'kudos-donations' ), $records );
		break;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Donors', 'kudos-donations' ); ?></h1>
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
	<form id="subscriptions-table" method="POST">
		<?php
		$table->display();
		?>
	</form>
</div>
