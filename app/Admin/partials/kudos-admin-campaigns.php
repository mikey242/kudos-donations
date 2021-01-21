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
$table_action = $table->current_action();

switch ( $table_action ) {
	case 'delete':
		$message = __( 'Campaign deleted', 'kudos-donations' );
		break;
	case 'bulk-delete':
		$records = count( $_REQUEST['bulk-action'] );
		$message = sprintf(
		/* translators: %s: Number of records */
			_n( 'Deleted %s campaign', 'Deleted %s campaigns', $records, 'kudos-donations' ),
			$records
		);
		break;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Campaigns', 'kudos-donations' ); ?></h1>
	<?php if ( ! empty( $_REQUEST['s'] ) ) { ?>
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
		printf('<p>' . __('Click <a href="%s">here</a> to edit and delete your campaigns'), admin_url( 'admin.php?page=kudos-settings&tabName=campaigns' ) . '</p>');
		$table->display();
		?>
	</form>
</div>
