<?php
/**
 * Transactions table view.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

use Kudos\Service\AdminNotice;

$table_action = $this->table->current_action();
$records      = isset( $_REQUEST['bulk-action'] ) ? count( $_REQUEST['bulk-action'] ) : 0;

switch ( $table_action ) {
	case 'delete':
		$message = __( 'Transaction deleted', 'kudos-donations' );
		break;
	case 'bulk-delete':
		$message = sprintf(
		/* translators: %s: Number of records. */
			_n( 'Deleted %s transaction', 'Deleted %s transactions', $records, 'kudos-donations' ),
			$records
		);
		break;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Transactions', 'kudos-donations' ); ?></h1>
	<?php if ( ! empty( $_REQUEST['s'] ) ) { ?>
		<span class="subtitle">
			<?php
			/* translators: %s: Search term */
			printf( __( 'Search results for “%s”', 'kudos-donations' ), $_REQUEST['s'] )
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
		<?php $this->table->display(); ?>
	</form>
</div>
