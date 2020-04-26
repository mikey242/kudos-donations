<?php

namespace Kudos\Transactions;

use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Transactions_Table extends WP_List_Table {

	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	public function extra_tablenav( $which ) {
		if ( $which == "top" ){
			//The code that goes before the table is here
			echo "Jouw recent Kudos transacties";
		}
	}

	public function no_items() {
		_e( 'Geen transacties beschikbaar.', 'kudos' );
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 */
	public function fetch_table_data() {
		global $wpdb;

		$table = $wpdb->prefix . Transaction::TABLE;
		$orderBy = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'time';
		$order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'DESC';
		$query = "SELECT
					*
				  FROM 
				  	$table
				  ORDER BY $orderBy $order";

		return $wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {
		return $columns= [
			'time'=>__('Datum', 'kudos'),
			'name'=>__('Naam', 'kudos'),
			'email'=>__('E-mail', 'kudos'),
			'value'=>__('Bedrag', 'kudos'),
			'status'=>__('Status', 'kudos'),
			'mode'=>__('Mode', 'kudos'),
		];
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 */
	public function get_hidden_columns()
	{
		return array();
	}

	/**
	 * Define the sortable columns
	 *
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
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$table_data = $this->fetch_table_data();
		usort( $table_data, array( &$this, 'sort_data' ) );

		$transactions_per_page = 10;
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
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item        Data
	 * @param  string $column_name - Current column name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'time':
			case 'name':
			case 'email':
			case 'value':
			case 'status':
			case 'mode':
				return $item[$column_name];
			default:
				return print_r( $item, true ) ;
		}
	}
}