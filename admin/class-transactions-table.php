<?php

namespace Kudos\Table;

use WP_List_Table;
use Kudos\Entity\Donor;
use Kudos\Kudos_Mapper;
use Kudos\Kudos_Invoice;
use Kudos\Table_Trait;
use Kudos\Entity\Transaction;

class Transactions extends WP_List_Table {

	use Table_Trait;

	/**
	 * @var string[]
	 */
	private $export_columns;
	/**
	 * @var Kudos_Mapper
	 */
	private $mapper;

	/**
	 * Class constructor
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		add_filter('table_export_row', [$this, 'modify_export_data']);
		$this->mapper = new Kudos_Mapper(Transaction::class);

		$this->export_columns = [
				'created' => __('Transaction created', 'kudos-donations'),
				'currency' => __('Currency', 'kudos-donations'),
				'value' => __('Amount', 'kudos-donations'),
				'refunds' => __('Refunded', 'kudos-donations'),
				'status' => __('Status', 'kudos-donations'),
				'name' => __('Name', 'kudos-donations'),
				'email' => __('Email', 'kudos-donations'),
				'method' => __('Method', 'kudos-donations'),
				'mode' => __('Mode', 'kudos-donations'),
				'sequence_type' => __('Type', 'kudos-donations'),
		];

		parent::__construct( [
			'table'    => $this->mapper->get_table_name(),
			'orderBy'  => 'created',
			'singular' => __( 'Transaction', 'kudos-donations' ), //singular name of the listed records
			'plural'   => __( 'Transactions', 'kudos-donations' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	/**
	 * Filter used to modify the exported data
	 *
	 * @param $row
	 * @return mixed
	 * @since   2.0.0
	 */
	function modify_export_data( $row ) {
		if(is_serialized($row['refunds'])) {
			$refunds = unserialize($row['refunds']);
			$refunded = $refunds['refunded'];
			$row['refunds'] = $refunded;
		}
		return $row;
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

		$status = (!empty($_GET['status']) ? sanitize_text_field($_GET['status']) : '');
		$customer_id = (!empty($_GET['customer_id']) ? sanitize_text_field($_GET['customer_id']) : '');
		$table = $this->mapper->get_table_name();

		// Add status if exist
		if($status) {
			array_push($query, $wpdb->prepare(
				"status = %s", esc_sql($status)
			));
		}

		// Add donor if exist
		if($customer_id) {
			array_push($query, $wpdb->prepare(
				"$table.customer_id = %s", esc_sql($customer_id)
			));
		}

		// Add search query if exist
		if(!empty($_REQUEST['s'])) {
			$search = esc_sql($_REQUEST['s']);
			array_push($query, $wpdb->prepare(
				'(`email` LIKE "%%%s%%") OR (`name` LIKE "%%%s%") OR (`order_id` LIKE "%%%s%")',
				$search, $search, $search
			));
		}

		$search_custom_vars = null;
		if($query) {
			$search_custom_vars = 'WHERE ' . implode(' AND ', $query);
		}

		return $this->mapper->get_table_data($search_custom_vars, [Donor::getTableName(), 'customer_id']);
	}

	/**
	 * Returns a list of columns to include in table
	 *
	 * @return array
	 * @since   2.0.0
	 */
	public function column_names() {
		return [
			'created'=>__('Date', 'kudos-donations'),
			'name'=>__('Name', 'kudos-donations'),
			'email'=>__('E-mail', 'kudos-donations'),
			'value'=>__('Amount', 'kudos-donations'),
			'type' => __('Type', 'kudos-donations'),
			'status'=>__('Status', 'kudos-donations'),
			'order_id'=>__('Order Id', 'kudos-donations'),
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
		$current = ( !empty($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all');

		//All link
		$count = count($this->count_records());
		$class = ($current == 'all' && empty($_REQUEST['s']) ? ' class="current"' :'');
		$all_url = remove_query_arg(['status', 'customer_id']);
		$views['all'] = "<a href='{$all_url }' {$class} >". __('All', 'kudos-donations') . " ($count)</a>";

		//Paid link
		$count = count($this->count_records('status', 'paid'));
		$paid_url = add_query_arg('status','paid');
		$class = ($current == 'paid' ? ' class="current"' :'');
		$views['paid'] = "<a href='{$paid_url}' {$class} >". __('Paid', 'kudos-donations') ." ($count)</a>";

		//Open link
		$count = count($this->count_records('status', 'open'));
		$open_url = add_query_arg('status','open');
		$class = ($current == 'open' ? ' class="current"' :'');
		$views['open'] = "<a href='{$open_url}' {$class} >". __('Open', 'kudos-donations') ." ($count)</a>";

		//Canceled link
		$count = count($this->count_records('status', 'canceled'));
		$canceled_url = add_query_arg('status','canceled');
		$class = ($current == 'canceled' ? ' class="current"' :'');
		$views['canceled'] = "<a href='{$canceled_url}' {$class} >". __('Canceled', 'kudos-donations') ." ($count)</a>";

		//Canceled link
		$count = count($this->count_records('status', 'expired'));
		$expired_url = add_query_arg('status','expired');
		$class = ($current == 'expired' ? ' class="current"' :'');
		$views['expired'] = "<a href='{$expired_url}' {$class} >". __('Expired', 'kudos-donations') ." ($count)</a>";

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
			'transaction_id',
			'donation_label',
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
			'created' => [
				'created',
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
	function column_created( $item ) {

		$delete_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = '<strong>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created'])) . '</strong>';
		$pdf = Kudos_Invoice::get_invoice($item['order_id']);

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

		$value = $item['value'];

		if(is_serialized($item['refunds'])) {
			$refund = unserialize($item['refunds']);
			$value = $refund['remaining'];
		}

		return '<i title="'.$item['method'].'" class="'. $icon .'"></i> '. $currency . ' ' . number_format_i18n($value, 2);

	}


	/**
	 * Payment type column
	 *
	 * @param $item
	 * @return string|void
	 * @since 2.0.0
	 */
	function column_type($item) {
		return get_sequence_type($item['sequence_type']);
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

		$invoice = Kudos_Invoice::get_invoice($item['order_id']);
		$refund = Kudos_Invoice::get_refund($item['order_id']);

		$refunded = $item['refunds'] ? __('Refunded', 'kudos-donations') : '';

		$return = $status;

		// Return as link if pdf invoice present
		if($invoice) {
			$return = "<a href=$invoice><i class='far fa-file-pdf'></i> " . $status . " " . "</a>";
		}

		if($refund) {
			$return .= "| <a href=$refund>" . $refunded . " " . "</a>";
		}

		return $return;
	}


	/**
	 * Order Id column
	 *
	 * @param $item
	 * @return string|void
	 * @since 2.0.0
	 */
	function column_order_id($item) {
		return $item['order_id'] . ($item['mode'] === 'test' ? ' ('. $item['mode'] .')' : '');
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
	 * @param $column
	 * @param int $order_id order ID
	 * @return false|int
	 * @since      1.0.0
	 */
	protected function delete_record( $column, $order_id ) {

		$result = $this->mapper->delete($column, $order_id);

		// Delete invoice if found
		if($result) {
			$file = Kudos_Invoice::get_invoice($order_id, true);
			if($file) {
				unlink($file);
			}
		}

		return $result;
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
					self::delete_record('order_id', sanitize_text_field( $_GET['transaction'] ) );
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
						self::delete_record( 'order_id', $id );
					}
				}
				break;

			case 'regenerate-invoices':
				Kudos_Invoice::regenerate_invoices();
		}
	}
}