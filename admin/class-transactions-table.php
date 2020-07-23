<?php

namespace Kudos;

use WP_List_Table;

class Transactions_Table extends WP_List_Table {

	use Table_Trait;

	/**
	 * @var Kudos_Invoice
	 */
	private $invoice;

	/**
	 * Class constructor
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->invoice = new Kudos_Invoice();

		parent::__construct( [
			'table'    => Kudos_Transaction::getTableName(),
			'orderBy'  => 'transaction_created',
			'singular' => __( 'Transaction', 'kudos-donations' ), //singular name of the listed records
			'plural'   => __( 'Transactions', 'kudos-donations' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @since      1.0.0
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ){

			//Export Button
			if($this->has_items()) {
				$export_nonce = wp_create_nonce( 'export-' . $this->_args['plural'] );
				$url = add_query_arg([
					'page' => !empty($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : '',
					'mode' => !empty($_REQUEST['mode']) ? esc_attr($_REQUEST['mode']) : '',
					'_wpnonce' => $export_nonce,
					'export_transactions' => ''

				]);
				echo "<a href='$url' class='button action'>". __('Export', 'kudos-donations') ."</a>";
			}
		}
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 * @since      1.0.0
	 */
	public function fetch_table_data() {
		global $wpdb;

		$query = [];

		$mode = (!empty($_GET['mode']) ? sanitize_text_field($_GET['mode']) : '');
		$customer_id = (!empty($_GET['customer_id']) ? sanitize_text_field($_GET['customer_id']) : '');

		// Add mode if exist
		if($mode) {
			array_push($query, $wpdb->prepare(
                "mode = %s", esc_sql($mode)
            ));
		}

		// Add donor if exist
		if($customer_id) {
			array_push($query, $wpdb->prepare(
				"d.customer_id = %s", esc_sql($customer_id)
			));
		}

		// Add search query if exist
		if(!empty($_REQUEST['s'])) {
			$search = esc_sql($_REQUEST['s']);
			array_push($query, $wpdb->prepare(
				'(`email` LIKE "%%%s%%") OR (`name` LIKE "%%%s%")',
				$search, $search
			));
		}

		$search_custom_vars = null;
		if($query) {
			$search_custom_vars = 'WHERE ' . implode(' AND ', $query);
		}

		$transaction = new Kudos_Transaction();
		return $transaction->get_table_data($search_custom_vars);
	}

	/**
	 * Column name translations used in export
	 *
	 * @param array $rows
	 * @return array
	 * @since   2.0.0
	 */
	public function export_column_names($rows) {

		// Set header names
		$headers = [];
		foreach (array_keys($rows[0]) as $header) {
			switch ($header) {
				case 'transaction_created':
					$result = __('Date', 'kudos-donations');
					break;
				case 'name':
					$result = __('Name', 'kudos-donations');
					break;
				case 'email':
					$result = __('Email', 'kudos-donations');
					break;
				case 'value':
					$result = __('Amount', 'kudos-donations');
					break;
				case 'status':
					$result = __('Status', 'kudos-donations');
					break;
				case 'method':
					$result = __('Method', 'kudos-donations');
					break;
				case 'mode':
					$result = __('Mode', 'kudos-donations');
					break;
				case 'currency':
					$result = __('Currency', 'kudos-donations');
					break;
				case 'sequenceType':
					$result = __('Type', 'kudos-donations');
					break;
				default:
					$result = ucfirst($header);
			}
			array_push($headers, $result);
		}

		return $headers;
	}

	/**
	 * Returns a list of columns to include in table
	 *
	 * @return array
	 * @since   2.0.0
	 */
	public function column_names() {
		return [
			'transaction_created'=>__('Date', 'kudos-donations'),
			'name'=>__('Name', 'kudos-donations'),
			'email'=>__('E-mail', 'kudos-donations'),
			'value'=>__('Amount', 'kudos-donations'),
			'type' => __('Type', 'kudos-donations'),
			'status'=>__('Status', 'kudos-donations'),
			'transaction_id'=>__('Transaction Id', 'kudos-donations'),
			'donation_label'=>__('Donation Label', 'kudos-donations')
		];
	}

	/**
	 * Gets view data
	 *
	 * @since      1.0.0
	 * @return array
	 */
	protected function get_views() {
		$views = [];
		$current = ( !empty($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'all');

		//All link
		$count = count($this->count_records());
		$class = ($current == 'all' ? ' class="current"' :'');
		$all_url = remove_query_arg('mode');
		$views['all'] = "<a href='{$all_url }' {$class} >". __('All', 'kudos-donations') . " ($count)</a>";

		//Test link
		$count = count($this->count_records('mode', 'test'));
		$test_url = add_query_arg('mode','test');
		$class = ($current == 'test' ? ' class="current"' :'');
		$views['test'] = "<a href='{$test_url}' {$class} >". __('Test', 'kudos-donations') ." ($count)</a>";

		//Live link
		$count = count($this->count_records('mode', 'live'));
		$live_url = add_query_arg('mode','live');
		$class = ($current == 'live' ? ' class="current"' :'');
		$views['live'] = "<a href='{$live_url}' {$class} >". __('Live', 'kudos-donations') ." ($count)</a>";
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
			'transaction_created' => [
				'transaction_created',
				false
			],
			'value' => [
				'value',
				false
			]
		];
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
			'<input type="checkbox" name="bulk-action[]" value="%s" />', $item['order_id']
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
	function column_transaction_created( $item ) {
		$delete_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = '<strong>' . date_i18n($item['transaction_created'], get_option('date_format') . ' ' . get_option('time_format')) . '</strong>';
		$invoice = $this->invoice;
		$pdf = $invoice->get_invoice($item['order_id']);

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&transaction=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', sanitize_text_field( $item['order_id'] ), $delete_nonce, __('Delete', 'kudos-donations') ),
			'view' => $pdf ? ' <a href="'.$pdf.'">'. __('Invoice') .'</a>' : ''
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


		$currency = !empty($item['currency']) ? get_currency_symbol($item['currency']) : '';

		return '<i title="'.$item['method'].'" class="'. $icon .'"></i> '. $currency . ' ' . number_format_i18n($item['value'], 2);

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

		$invoice = $this->invoice;
		$pdf = $invoice->get_invoice($item['order_id']);

		$mode = $item['mode'] === 'test' ? ' ('. $item['mode'] .')' : '';
		$return = $status . ' ' . $mode;

		// Return as link if pdf invoice present
		if($pdf) {
			return "<a href=$pdf>" . $status . $mode . " " . "<i class='far fa-file-pdf'></i></a>";
		}

		return $return;
	}

	function column_type($item) {
		return get_sequence_type($item['sequence_type']);
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since      1.0.0
	 * @return array|string[]
	 */
	function get_bulk_actions() {
		return [
			'bulk-delete'   => __('Delete', 'kudos-donations'),
		];
	}

	/**
	 * Delete a transaction.
	 *
	 * @since      1.0.0
	 * @param int $order_id order ID
	 */
	public static function delete_transaction( $order_id ) {
		global $wpdb;

		$result = $wpdb->delete(
			Kudos_Transaction::getTableName(),
			[ 'order_id' => $order_id ]
		);

		// Delete invoice if found
		if($result) {
			$invoice = new Kudos_Invoice();
			$file = $invoice->get_invoice($order_id, true);
			if($file) {
				unlink($file);
			}
		}
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
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				} else {
					self::delete_transaction( sanitize_text_field( $_GET['transaction'] ) );
				}
				break;

			case 'bulk-delete':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if(isset($_REQUEST['bulk-action'])) {

					$delete_ids = esc_sql( $_REQUEST['bulk-action']);
					foreach ( $delete_ids as $id ) {
						self::delete_transaction( $id );
					}
				}
				break;

			case 'regenerate-invoices':
				Kudos_Invoice::regenerate_invoices();
		}
	}
}