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
$action = $table->current_action();

switch ( $action ) {
	case 'delete':
		$message = __( 'Donor deleted', 'kudos-donations' );
		break;
	case 'bulk-delete':
		$message = sprintf( __( '%s donor(s) deleted', 'kudos-donations' ), count( $_REQUEST['bulk-action'] ) );
		break;
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Donors', 'kudos-donations' ); ?></h1>
	<?php if ( ! empty( $_REQUEST['s'] ) ) { ?>
        <span class="subtitle">
                <?php
                /* translators: %s: Search term */
                printf( __( 'Search results for “%s”' ), $_REQUEST['s'] )
                ?>
            </span>
	<?php } ?>
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
