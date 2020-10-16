<?php

namespace Kudos\Admin\Table;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use WP_List_Table;

class TransactionsTable extends WP_List_Table {

	use TableTrait;

	/**
	 * @var MapperService
	 */
	private $mapper;

	/**
	 * Class constructor
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->mapper = new MapperService( TransactionEntity::class );
		$this->table  = TransactionEntity::TABLE;

		$this->export_columns = [
			'created'       => __( 'Transaction created', 'kudos-donations' ),
			'currency'      => __( 'Currency', 'kudos-donations' ),
			'value'         => __( 'Amount', 'kudos-donations' ),
			'refunds'       => __( 'Refunded', 'kudos-donations' ),
			'status'        => __( 'Status', 'kudos-donations' ),
			'name'          => __( 'Name', 'kudos-donations' ),
			'email'         => __( 'Email', 'kudos-donations' ),
			'method'        => __( 'Method', 'kudos-donations' ),
			'mode'          => __( 'Mode', 'kudos-donations' ),
			'sequence_type' => __( 'Type', 'kudos-donations' ),
		];

		parent::__construct( [
			'orderBy'  => 'created',
			'singular' => __( 'Transaction', 'kudos-donations' ), //singular name of the listed records
			'plural'   => __( 'Transactions', 'kudos-donations' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	/**
	 * Call this function where the table is to be displayed
	 *
	 * @since      1.0.0
	 */
	public function display() {

		$this->views();
		$this->search_box( __( 'Search' ) . ' ' . $this->_args['plural'], 'search_records' );
		parent::display();

	}

	/**
	 * Get the table data
	 *
	 * @return array
	 * @since   1.0.0
	 */
	public function fetch_table_data() {

		global $wpdb;

		$query      = [];
		$table      = $wpdb->prefix . $this->table;
		$join_table = DonorEntity::getTableName();

		$status = ( ! empty( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '' );

		// Add status if exist
		if ( $status ) {
			array_push( $query,
				$wpdb->prepare(
					"status = %s",
					esc_sql( $status )
				) );
		}

		// Add search query if exist
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search = esc_sql( $_REQUEST['s'] );
			array_push( $query,
				"(${join_table}.email LIKE '${search}') OR (${join_table}.name LIKE '${search}') OR (order_id LIKE '${search}') OR (transaction_id LIKE '${search}') OR (campaign_label LIKE '${search}')"
			);
		}

		$search_custom_vars = null;
		if ( $query ) {
			$search_custom_vars = 'WHERE ' . implode( ' AND ', $query );
		}

		$search_custom_vars = " LEFT JOIN $join_table on $join_table.customer_id = $table.customer_id " . $search_custom_vars;

		return $wpdb->get_results( "
			SELECT $table.*, $join_table.name, $join_table.email
			FROM $table
			$search_custom_vars
		",
			ARRAY_A );

	}

	/**
	 * Returns a list of columns to include in table
	 *
	 * @return array
	 * @since   2.0.0
	 */
	public function column_names() {

		return [
			'created'        => __( 'Date', 'kudos-donations' ),
			'name'           => __( 'Name', 'kudos-donations' ),
			'email'          => __( 'E-mail', 'kudos-donations' ),
			'value'          => __( 'Amount', 'kudos-donations' ),
			'type'           => __( 'Type', 'kudos-donations' ),
			'status'         => __( 'Status', 'kudos-donations' ),
			'order_id'       => __( 'Order ID', 'kudos-donations' ),
			'transaction_id' => __( 'Transaction Id', 'kudos-donations' ),
			'campaign_label' => __( 'Campaign', 'kudos-donations' ),
		];

	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 * @since   1.0.0
	 */
	public function get_hidden_columns() {
		return [
			'transaction_id',
//			'campaign_label',
		];

	}

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 * @since   1.0.0
	 */
	public function get_sortable_columns() {

		return [
			'created'        => [
				'created',
				false,
			],
			'value'          => [
				'value',
				false,
			],
			'campaign_label' => [
				'campaign_label',
				false,
			],
		];

	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 * @since   1.0.0
	 */
	function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />',
			$item['order_id']
		);

	}

	/**
	 * Time (date) column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 * @since   1.0.0
	 */
	function column_created( array $item ) {

		$delete_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = '<strong>' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				strtotime( $item['created'] ) ) . '</strong>';

		$order_id = $item['order_id'];

		$actions = apply_filters( TransactionEntity::TABLE . "_actions",
			[
				'delete' => sprintf( '<a href="?page=%s&action=%s&order_id=%s&_wpnonce=%s">%s</a>',
					esc_attr( $_REQUEST['page'] ),
					'delete',
					$order_id,
					$delete_nonce,
					__( 'Delete', 'kudos-donations' ) ),
			],
			$order_id );

		return $title . $this->row_actions( $actions );

	}

	/**
	 * Name column
	 *
	 * @param array $item
	 *
	 * @return string|null
	 * @since   2.0.0
	 */
	function column_name( array $item ) {

		$email = $item['email'];

		if ( $email ) {
			return sprintf(
				"<a href='%s' />%s</a>",
				admin_url( sprintf( "admin.php?page=kudos-donors&s=%s", $email ) ),
				$item['name']
			);
		}

		return $item['name'];

	}

	/**
	 * Email column
	 *
	 * @param array $item
	 *
	 * @return string
	 * @since   1.0.0
	 */
	function column_email( array $item ) {

		return sprintf(
			'<a href="mailto: %1$s" />%1$s</a>',
			$item['email']
		);

	}

	/**
	 * Value (amount) column
	 *
	 * @param array $item
	 *
	 * @return string|void
	 * @since   1.0.0
	 */
	function column_value( array $item ) {

		switch ( $item['method'] ) {
			case 'ideal':
				$icon = 'fab fa-ideal';
				break;
			case 'creditcard':
				$icon = 'fas fa-credit-card';
				break;
			case 'paypal':
				$icon = 'fab fa-paypal';
				break;
			default:
				$icon = '';
				break;
		}

		$currency = ! empty( $item['currency'] ) ? Utils::get_currency_symbol( $item['currency'] ) : '';

		$value = $item['value'];

		/** @var TransactionEntity $transaction */
		$transaction = $this->mapper->get_one_by( [ 'order_id' => $item['order_id'] ] );
		$refund      = $transaction->get_refund();
		if ( $refund ) {
			$value = $refund['remaining'];
		}

		return '<i title="' . $item['method'] . '" class="' . $icon . '"></i> ' . $currency . ' ' . number_format_i18n( $value,
				2 );

	}

	/**
	 * Payment type column
	 *
	 * @param array $item
	 *
	 * @return string|void
	 * @since   2.0.0
	 */
	function column_type( array $item ) {

		return Utils::get_sequence_type( $item['sequence_type'] );

	}

	/**
	 * Payment status column
	 *
	 * @param array $item
	 *
	 * @return string|void
	 * @since   1.0.0
	 */
	function column_status( array $item ) {

		switch ( $item['status'] ) {
			case 'paid':
				$status = __( 'Paid', 'kudos-donations' );
				break;
			case 'open':
				$status = __( 'Open', 'kudos-donations' );
				break;
			case 'expired':
				$status = __( 'Expired', 'kudos-donations' );
				break;
			case 'canceled':
				$status = __( 'Cancelled', 'kudos-donations' );
				break;
			case 'failed':
				$status = __( 'Failed', 'kudos-donations' );
				break;
			default:
				$status = __( 'Unknown', 'kudos-donations' );
		}

		return apply_filters( 'kudos_transactions_column_status', $status, $item['order_id'] );

	}

	/**
	 * Order Id column
	 *
	 * @param array $item
	 *
	 * @return string|void
	 * @since   2.0.0
	 */
	function column_order_id( array $item ) {

		return $item['order_id'] . ( $item['mode'] === 'test' ? ' (' . $item['mode'] . ')' : '' );

	}

	/**
	 * Return campaign label as a search link
	 *
	 * @param array $item
	 * @return string
	 * @since 2.0.2
	 */
	function column_campaign_label( array $item ) {

		return sprintf('<a href=%1$s>%2$s</a>',
			sprintf(admin_url( 'admin.php?page=kudos-transactions&s=%s'), urlencode( $item['campaign_label'] ) ),
			strtoupper($item['campaign_label'])
		);

	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array|string
	 * @since   1.0.0
	 */
	function get_bulk_actions() {

		return [
			'bulk-delete' => __( 'Delete', 'kudos-donations' ),
		];

	}

	/**
	 * Process delete and bulk-delete actions
	 *
	 * @since   1.0.0
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		switch ( $this->current_action() ) {

			case 'delete':

				// Verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				}

				self::delete_record( 'order_id', sanitize_text_field( $_GET['order_id'] ) );
				break;

			case 'bulk-delete':

				// Verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if ( isset( $_REQUEST['bulk-action'] ) ) {

					$delete_ids = esc_sql( $_REQUEST['bulk-action'] );
					foreach ( $delete_ids as $id ) {
						self::delete_record( 'order_id', sanitize_text_field( $id ) );
					}
				}
				break;
		}

	}

	/**
	 * Delete a transaction.
	 *
	 * @param $column
	 * @param string $order_id order ID
	 *
	 * @return false|int
	 * @since   1.0.0
	 */
	protected function delete_record( $column, string $order_id ) {

		$result = $this->mapper->delete( $column, $order_id );

		// Add action if transaction deleted
		if ( $result ) {
			do_action( 'kudos_delete_transaction', $order_id );
		}

		return $result;

	}

	/**
	 * Gets view data
	 *
	 * @return array
	 * @since   1.0.0
	 */
	protected function get_views() {

		$views   = [];
		$current = ( ! empty( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all' );

		// Remove search query from current url
		$url = remove_query_arg( 's' );

		//All link
		$count        = count( $this->count_records() );
		$class        = ( $current == 'all' && empty( $_REQUEST['s'] ) ? ' class="current"' : '' );
		$all_url      = remove_query_arg( [ 'status' ], $url );
		$views['all'] = "<a href='{$all_url }' {$class} >" . __( 'All', 'kudos-donations' ) . " ($count)</a>";

		//Paid link
		$count         = count( $this->count_records( 'status', 'paid' ) );
		$paid_url      = add_query_arg( 'status', 'paid', $url );
		$class         = ( $current == 'paid' ? ' class="current"' : '' );
		$views['paid'] = "<a href='{$paid_url}' {$class} >" . __( 'Paid', 'kudos-donations' ) . " ($count)</a>";

		//Open link
		$count         = count( $this->count_records( 'status', 'open' ) );
		$open_url      = add_query_arg( 'status', 'open', $url );
		$class         = ( $current == 'open' ? ' class="current"' : '' );
		$views['open'] = "<a href='{$open_url}' {$class} >" . __( 'Open', 'kudos-donations' ) . " ($count)</a>";

		//Canceled link
		$count             = count( $this->count_records( 'status', 'canceled' ) );
		$canceled_url      = add_query_arg( 'status', 'canceled', $url );
		$class             = ( $current == 'canceled' ? ' class="current"' : '' );
		$views['canceled'] = "<a href='{$canceled_url}' {$class} >" . __( 'Cancelled',
				'kudos-donations' ) . " ($count)</a>";

		//Canceled link
		$count            = count( $this->count_records( 'status', 'expired' ) );
		$expired_url      = add_query_arg( 'status', 'expired', $url );
		$class            = ( $current == 'expired' ? ' class="current"' : '' );
		$views['expired'] = "<a href='{$expired_url}' {$class} >" . __( 'Expired',
				'kudos-donations' ) . " ($count)</a>";

		return $views;

	}
}