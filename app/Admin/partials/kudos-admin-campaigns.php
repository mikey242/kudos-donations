<?php

use Kudos\Admin\Table\CampaignsTable;
use Kudos\Service\AdminNotice;

/**
 * Creates the donors table
 *
 * @since    1.1.0
 */

$table = new CampaignsTable();
$table->prepare_items();
$action = $table->current_action();

switch ( $action ) {
	case 'delete':
		$message = __( 'Campaign deleted', 'kudos-donations' );
		break;
	case 'bulk-delete':
		$records = count( $_REQUEST['bulk-action'] );
		/* translators: %s: Number of records */
		$message = sprintf( _n( 'Deleted %s campaign', 'Deleted %s campaigns', $records, 'kudos-donations' ),
			$records );
		break;
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Campaigns', 'kudos-donations' ); ?></h1>
	<?php if ( isset( $message ) ) {
		$notice = new AdminNotice( esc_html( $message ) );
		$notice->render();
	} ?>
    <form id="subscriptions-table" method="POST">
		<?php
		$table->display();
		?>
    </form>
</div>
