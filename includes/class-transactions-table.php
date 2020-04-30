<?php

/*
 * @source https://gist.github.com/paulund/7659452
 */

namespace Kudos\Transactions;

use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Transactions_Table extends WP_List_Table {

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @since      1.0.0
	 *
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	public function extra_tablenav( $which ) {
		if ( $which == "top" ){
			//The code that goes before the table is here
			echo "Jouw recent Kudos transacties";
		}
	}

	public function display() {
		$this->views();
		parent::display();
	}

	public function no_items() {
		_e( 'Geen transacties beschikbaar.', 'kudos' );
	}

	/**
	 * Get the table data
	 *
	 * @since      1.0.0
	 *
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
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @since      1.0.0
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

	protected function get_views() {
		$views = array();
		$current = ( !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : 'all');

		//All link
		$class = ($current == 'all' ? ' class="current"' :'');
		$all_url = remove_query_arg('mode');
		$views['all'] = "<a href='{$all_url }' {$class} >All</a>";

		//Test link
		$test_url = add_query_arg('mode','test');
		$class = ($current == 'test' ? ' class="current"' :'');
		$views['test'] = "<a href='{$test_url}' {$class} >Test</a>";

		//Live link
		$live_url = add_query_arg('mode','live');
		$class = ($current == 'live' ? ' class="current"' :'');
		$views['live'] = "<a href='{$live_url}' {$class} >Live</a>";
		return $views;

	}

	/**
	 * Define which columns are hidden
	 *
	 * @since      1.0.0
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
	 * @since      1.0.0
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
	 *
	 * @since      1.0.0
	 *
	 * @return void
	 */
	function prepare_items() {

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
	 * Define what data to show on each column of the table
	 *
	 * @since      1.0.0
	 *
	 * @param  array $item        Data
	 * @param  string $column_name - Current column name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {

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

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @since      1.0.0
	 *
	 * @param $a
	 * @param $b
	 *
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
}