<?php

namespace Kudos;

use WP_List_Table;

class Donors_Table extends WP_List_Table {

	use Table_Trait;

	/**
	 * Class constructor
	 *
	 * @since      1.1.0
	 */
	public function __construct() {

		parent::__construct( [
			'table'    => Kudos_Donor::getTableName(),
			'orderBy'  => 'donor_created',
			'singular' => __( 'Donor', 'kudos-donations' ),
			'plural'   => __( 'Donors', 'kudos-donations' ),
			'ajax'     => false
		] );

	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @since      1.1.0
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {

		if ( $which == "top" ){
			//Export Button
			if($this->has_items()) {
				$export_nonce = wp_create_nonce( 'export-' . $this->_args['plural'] );
				$url = add_query_arg([
					'page' => !empty($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : '',
					'_wpnonce' => $export_nonce,
					'export_donors' => ''

				]);
				echo "<a href='$url' class='button action'>". __('Export', 'kudos-donations') ."</a>";
			}
		}
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 * @since      1.1.0
	 */
	public function fetch_table_data() {
		global $wpdb;

		$search_custom_vars = null;

		// Add search query if exist
		if(!empty($_REQUEST['s'])) {
			$search = esc_sql($_REQUEST['s']);
			$search_custom_vars .= $wpdb->prepare(
				($search_custom_vars ? " AND" : " WHERE") . " (`email` LIKE '%%%s%%') OR (`name` LIKE '%%%s%')",
				$search, $search
			);
		}

		$subscription = new Kudos_Donor();
		return $subscription->get_table_data($search_custom_vars);
	}

	/**
	 * Column name translations used in export
	 *
	 * @param array $rows
	 * @return array
	 * @since   1.1.0
	 */
	public function export_column_names($rows) {

		// Set header names
		$headers = [];
		foreach (array_keys($rows[0]) as $header) {
			switch ($header) {
				case 'donor_created':
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
				case 'frequency':
					$result = __('Frequency', 'kudos-donations');
					break;
				case 'mode':
					$result = __('Mode', 'kudos-donations');
					break;
				case 'currency':
					$result = __('Currency', 'kudos-donations');
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
	 * @since   1.1.0
	 */
	public function column_names() {
		return [
			'donor_created'=>__('Date', 'kudos-donations'),
			'name' => __('Name', 'kudos-donations'),
			'email'=>__('E-mail', 'kudos-donations'),
			'address' => __('Address', 'kudos-donations'),
			'donations' => __('Donations', 'kudos-donations')
		];
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since      1.1.0
	 * @return array
	 */
	public function get_hidden_columns()
	{
		return [
			'subscription_id',
			'id'
		];
	}

	/**
	 * Define the sortable columns
	 *
	 * @since      1.1.0
	 * @return array
	 */
	public function get_sortable_columns()
	{
		return [
			'donor_created' => [
				'donor_created',
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
	 * @since      1.1.0
	 * @param array $item
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />', $item['customer_id']
		);
	}

	/**
	 * Time (date) column
	 *
	 * @since      1.0.0
	 * @param array $item an array of DB data
	 * @return string
	 */
	function column_donor_created( $item ) {

		$delete_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = '<strong>' . date_i18n($item['donor_created'], get_option('date_format') . ' ' . get_option('time_format')) . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&transaction=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', sanitize_text_field( $item['customer_id'] ), $delete_nonce, __('Delete', 'kudos-donations') ),
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Email column
	 *
	 * @since      1.1.0
	 * @param array $item
	 * @return string
	 */
	function column_email( $item ) {
		return sprintf(
			'<a href="mailto: %1$s" />%1$s</a>', $item['email']
		);
	}

	/**
	 * Address column
	 *
	 * @since      1.1.0
	 * @param array $item
	 * @return string
	 */
	function column_address( $item ) {

		$address = [
			$item['street'],
			$item['postcode'] . ' ' . $item['city'],
			$item['country']
		];

		return implode('<br/>', $address);
	}

	/**
	 * Donations column
	 *
	 * @since      1.1.0
	 * @param array $item
	 * @return string
	 */
	function column_donations( $item ) {

		$kudos_transaction = new Kudos_Transaction();
		$transactions = $kudos_transaction->get_all_by(['customer_id' => $item['customer_id']]);

		if($transactions) {
			$number = count($transactions);
			$total = 0;
			foreach ($transactions as $transaction) {
				$total = $total + $transaction->value;
			}

			return '<a href="'. admin_url('admin.php?page=kudos-transactions&customer_id='. urlencode($item['customer_id']) .'') .'">' . $number . ' ( ' . get_currency_symbol($transactions[0]->currency) . $total . ' )' . '</a>';
		}

		return false;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since      1.1.0
	 * @return array|string[]
	 */
	function get_bulk_actions() {
		return [
			'bulk-delete'   => __('Delete', 'kudos-donations'),
		];
	}

	/**
	 * Process cancel and bulk-cancel actions
	 *
	 * @since      1.1.0
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		switch ($this->current_action()) {

			case 'delete':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				} else {
					self::cancel_subscription( sanitize_text_field( $_GET['customer_id'] ) );
				}
				break;

			case 'bulk-delete':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if(isset($_REQUEST['bulk-action'])) {
					$cancel_ids = esc_sql( $_REQUEST['bulk-action']);
					foreach ( $cancel_ids as $id ) {
						self::cancel_subscription( $id );
					}
				}
				break;
		}
	}
}