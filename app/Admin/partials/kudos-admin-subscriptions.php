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
$table_action = $table->current_action();
$records      = isset( $_REQUEST['bulk-action'] ) ? count( $_REQUEST['bulk-action'] ) : 0;

switch ( $table_action ) {
	case 'cancel':
		$message = __( 'Subscription cancelled', 'kudos-donations' );
		break;
	case 'delete':
		$message = __( 'Subscription deleted', 'kudos-donations' );
		break;
	case 'bulk-cancel':
		$message = sprintf(
		/* translators: %s: Number of records */
			_n( 'Cancelled %s subscription', 'Cancelled %s subscriptions', $records, 'kudos-donations' ),
			$records
		);
		break;
	case 'bulk-delete':
		$message = sprintf(
		/* translators: %s: Number of records */
			_n( 'Deleted %s subscription', 'Deleted %s subscriptions', $records, 'kudos-donations' ),
			$records
		);
		break;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Subscriptions', 'kudos-donations' ); ?></h1>
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
		<?php $table->display(); ?>
	</form>
</div>
