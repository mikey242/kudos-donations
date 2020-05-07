<?php

/*
 * https://gist.github.com/paulund/7659452
 * https://github.com/pmbaldha/WP-Custom-List-Table-With-Database-Example/blob/master/custom-list-table-db-example.php
 * https://github.com/collizo4sky/WP_List_Table-Class-Plugin-Example/blob/master/plugin.php
 */

namespace Kudos;

use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Transactions_Table extends WP_List_Table {

	/**
	 * Class constructor
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		parent::__construct( [
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
		parent::display();
	}

	/**
	 * Message to show when no transactions available
	 *
	 * @since      1.0.0
	 */
	public function no_items() {
		_e( 'Geen transacties beschikbaar.', 'kudos-donations' );
	}

	/**
	 * Get the table data
	 *
	 * @since      1.0.0
	 * @return array
	 */
	public function fetch_table_data() {
		global $wpdb;

		$mode = (!empty($_GET['mode']) ? $_GET['mode'] : '');

		$search_custom_vars = '';

		if($mode) {
			$search_custom_vars = "WHERE mode LIKE '%" . esc_sql($mode) ."%'";
		}

		$table = $wpdb->prefix . Transaction::TABLE;
		$query = "SELECT
					*
				  FROM 
				  	$table
				  $search_custom_vars
				  	";

		return $wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * Columns to show
	 *
	 * @since      1.0.0
	 * @return array
	 */
	public function get_columns() {
		return $columns= [
			'cb' => '<input type="checkbox" />',
			'time'=>__('Date', 'kudos-donations'),
			'name'=>__('Name', 'kudos-donations'),
			'email'=>__('E-mail', 'kudos-donations'),
			'value'=>__('Amount', 'kudos-donations'),
			'status'=>__('Status', 'kudos-donations'),
			'transaction_id'=>__('Transaction Id', 'kudos-donations')
		];
	}

	/**
	 * Gets view data
	 *
	 * @since      1.0.0
	 * @return array
	 */
	protected function get_views() {
		$views = array();
		$current = ( !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : 'all');

		//All link
		$class = ($current == 'all' ? ' class="current"' :'');
		$all_url = remove_query_arg('mode');
		$views['all'] = "<a href='{$all_url }' {$class} >". __('All', 'kudos-donations') ."</a>";

		//Test link
		$test_url = add_query_arg('mode','test');
		$class = ($current == 'test' ? ' class="current"' :'');
		$views['test'] = "<a href='{$test_url}' {$class} >". __('Test', 'kudos-donations') ."</a>";

		//Live link
		$live_url = add_query_arg('mode','live');
		$class = ($current == 'live' ? ' class="current"' :'');
		$views['live'] = "<a href='{$live_url}' {$class} >". __('Live', 'kudos-donations') ."</a>";
		return $views;

	}

	/**
	 * Define which columns are hidden
	 *
	 * @since      1.0.0
	 * @return array
	 */
	public function get_hidden_columns()
	{
		return [
			'transaction_id'
		];
	}

	/**
	 * Define the sortable columns
	 *
	 * @since      1.0.0
	 * @return array
	 */
	public function get_sortable_columns()
	{
		return [
			'time' => [
				'time',
				false
			],
			'value' => [
				'value',
				false
			]
		];
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since      1.0.0
	 * @param  array $item        Data
	 * @param  string $column_name - Current column name
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'name':
			case 'transaction_id':
				return $item[$column_name];
			default:
				return print_r( $item, true ) ;
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since      1.0.0
	 * @param array $item
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Email column
	 *
	 * @since      1.0.0
	 * @param array $item
	 * @return string
	 */
	function column_email( $item ) {
		return sprintf(
			'<a href="mailto: %1$s" />%1$s</a>', $item['email']
		);
	}

	/**
	 * Time (date) column
	 *
	 * @since      1.0.0
	 * @param array $item an array of DB data
	 * @return string
	 */
	function column_time( $item ) {

		$delete_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = '<strong>' . date_i18n($item['time'], get_option('date_format') . ' ' . get_option('time_format')) . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&transaction=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Value (amount) column
	 *
	 * @since      1.0.0
	 * @param array $item
	 * @return string|void
	 */
	function column_value($item)
	{

		switch ($item['method']) {
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

		return '<i title="'.$item['method'].'" class="'. $icon .'"></i> € ' . number_format_i18n($item['value'], 2);

	}

	/**
	 * Payment status column
	 *
	 * @since      1.0.0
	 * @param array $item
	 * @return string|void
	 */
	function column_status($item)
	{

		switch ($item['status']) {
			case 'paid':
				$status = __('Paid', 'kudos-donations');
				break;
			case 'open':
				$status = __('Open', 'kudos-donations');
				break;
			case 'expired':
				$status = __('Expired', 'kudos-donations');
				break;
			case 'canceled':
				$status = __('Canceled', 'kudos-donations');
				break;
			case 'failed':
				$status = __('Failed', 'kudos-donations');
				break;
			default:
				$status = __('Unknown', 'kudos-donations');
		}

		return $status . ($item['mode'] === 'test' ? ' ('. $item['mode'] .')' : '');
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since      1.0.0
	 * @return array|string[]
	 */
	function get_bulk_actions() {
		return [
			'bulk-delete'    => 'Delete'
		];
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 *
	 * @since      1.0.0
	 * @return void
	 */
	function prepare_items() {

		// Process bulk action if any
		$this->process_bulk_action();

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$table_data = $this->fetch_table_data();
		usort( $table_data, array( &$this, 'sort_data' ) );

		$transactions_per_page = 20;
		$current_page = $this->get_pagenum();
		$this->items = array_slice( $table_data, ( ( $current_page - 1 ) * $transactions_per_page ), $transactions_per_page );
		$total_transactions = count( $table_data );
		$this->set_pagination_args( [
			'total_items' => '10',
			'per_page'    => $transactions_per_page,
			'total_pages' => ceil( $total_transactions/$transactions_per_page )
		] );
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @since      1.0.0
	 * @param $a
	 * @param $b
	 * @return Mixed
	 */
	private function sort_data( $a, $b )
	{
		// Set defaults
		$orderBy = 'time';
		$order = 'desc';

		// If orderBy is set, use this as the sort column
		if(!empty($_GET['orderby']))
		{
			$orderBy = $_GET['orderby'];
		}

		// If order is set use this as the order
		if(!empty($_GET['order']))
		{
			$order = $_GET['order'];
		}

		$result = strcmp( $a[$orderBy], $b[$orderBy] );

		if($order === 'asc')
		{
			return $result;
		}

		return -$result;
	}


	/**
	 * Delete a transaction.
	 *
	 * @since      1.0.0
	 * @param int $id transaction ID
	 */
	public static function delete_transaction( $id ) {
		global $wpdb;

		$wpdb->delete(
			$table = $wpdb->prefix . Transaction::TABLE,
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Process delete and bulk-delete actions
	 *
	 * @since      1.0.0
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		switch ($this->current_action()) {

			case 'delete':
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
				if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['singular'] ) ) {
					die();
				} else {
					self::delete_transaction( absint( $_GET['transaction'] ) );
				}
				break;

			case 'bulk-delete':
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
				if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
					die();

				}

				if(isset($_REQUEST['bulk-delete'])) {
					$delete_ids = esc_sql( $_REQUEST['bulk-delete']);
					foreach ( $delete_ids as $id ) {
						self::delete_transaction( $id );
					}
				}

				break;
		}
	}
}