<?php

/*
 * https://gist.github.com/paulund/7659452
 * https://github.com/pmbaldha/WP-Custom-List-Table-With-Database-Example/blob/master/custom-list-table-db-example.php
 * https://github.com/collizo4sky/WP_List_Table-Class-Plugin-Example/blob/master/plugin.php
 */

namespace Kudos;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

trait Table_Trait {

	/**
	 * Call this function where the table is to be displayed
	 *
	 * @since      1.0.0
	 */
	public function display() {
		$this->views();
		$this->search_box(__('Search') . ' ' . $this->_args['plural'], 'search_records');
		parent::display();
	}

	/**
	 * Message to show when no transactions available
	 *
	 * @since      1.0.0
	 */
	public function no_items() {
		printf(__( 'No %s found.', 'kudos-donations' ), $this->_args['singular']);
	}

	/**
	 * @param null|string $column
	 * @param null|string $value
	 *
	 * @return array|object|null
	 * @since   2.0.0
	 */
	public function count_records( $column=null, $value=null ) {
		global $wpdb;

		$search_custom_vars = '';

		if($column && $value) {
			$search_custom_vars = $wpdb->prepare(
				"WHERE ". $column . " = %s", esc_sql($value)
			);
		}

		$table = $this->table;
		$query = "SELECT * FROM `$table`
				  $search_custom_vars";

		return $wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * Columns to show
	 *
	 * @since      2.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns['cb'] = '<input type="checkbox" />';
		return array_merge($columns, static::column_names());
	}
	/**
	 * Exports all data to a csv file
	 *
	 * @since      1.0.1
	 */
	public function export() {

		// Check nonce
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( ! wp_verify_nonce( $nonce, 'export-' .$this->_args['plural'] ) ) {
			die();
		}

		// Get rows and end if no data
		$rows = static::fetch_table_data();
		if(!$rows) {
			return;
		}

		// Remove rows not in export columns and rearrange order to match
		foreach ($rows as $key=>$value) {
			$rows[$key] = array_merge($this->export_columns, array_intersect_key($rows[$key], $this->export_columns));
		}

		$filename = "kudos_". __($this->_args['plural'], 'kudos-donations') ."-" . date( "Y-m-d_H-i", time() ) . '.csv';
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=$filename;");
		header("Content-Transfer-Encoding: binary");

		// Start output
		$out = fopen( 'php://output', 'w' );

		// Add headers
		$headers = array_values($this->export_columns);
		fputcsv($out, $headers);

		// Add rows
		foreach ( $rows as $row ) {
			$row = apply_filters('table_export_row', $row);
			fputcsv( $out, $row);
		}

		// Close output
		fclose( $out );
		exit();
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

		return $item[$column_name];

	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 *
	 * @since      1.0.0
	 * @return void
	 */
	function prepare_items() {

		// Process bulk action if any
		static::process_bulk_action();

		$columns = $this->get_columns();
		$hidden = static::get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$table_data = static::fetch_table_data();
		usort( $table_data, [&$this, 'sort_data']);

		$items_per_page = 20;
		$current_page = $this->get_pagenum();
		$this->items = array_slice( $table_data, ( ( $current_page - 1 ) * $items_per_page ), $items_per_page );
		$total_items = count( $table_data );
		$this->set_pagination_args( [
			'total_items' => count($this->items),
			'per_page'    => $items_per_page,
			'total_pages' => ceil( $total_items/$items_per_page )
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
		$orderBy = $this->_args['orderBy'] ?? 'time';
		$order = $this->_args['order'] ?? 'desc';

		// If orderBy is set, use this as the sort column
		if(!empty($_GET['orderby']))
		{
			$orderBy = $_GET['orderby'];
		}

		// If order is set use this as the order
		if(!empty($_GET['order']))
		{
			$order = sanitize_text_field($_GET['order']);
		}

		$result = strcmp( $a[$orderBy], $b[$orderBy] );

		if($order === 'asc')
		{
			return $result;
		}

		return -$result;
	}
}