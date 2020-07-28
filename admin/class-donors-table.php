<?php

namespace Kudos\Table;

use Kudos\Entity\Transaction;
use WP_List_Table;
use Kudos\Table_Trait;
use Kudos\Entity\Donor;

class Donors extends WP_List_Table {

	use Table_Trait;

	/**
	 * @var array
	 */
	private $export_columns;

	/**
	 * Class constructor
	 *
	 * @since      2.0.0
	 */
	public function __construct() {

		$this->export_columns = [
			'name' => __('Name', 'kudos-donations'),
			'email' => __('Email', 'kudos-donations'),
			'street' => __('Street', 'kudos-donations'),
			'postcode' => __('Postcode', 'kudos-donations'),
			'city' => __('City', 'kudos-donations'),
			'country' => __('Country', 'kudos-donations'),
		];

		parent::__construct( [
			'table'    => Donor::getTableName(),
			'orderBy'  => 'donor_created',
			'singular' => __( 'Donor', 'kudos-donations' ),
			'plural'   => __( 'Donors', 'kudos-donations' ),
			'ajax'     => false
		] );

	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @since      2.0.0
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
	 * @since      2.0.0
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

		return Donor::get_table_data($search_custom_vars);
	}

	/**
	 * Returns a list of columns to include in table
	 *
	 * @return array
	 * @since   2.0.0
	 */
	public function column_names() {
		return [
			'email'=>__('E-mail', 'kudos-donations'),
			'name' => __('Name', 'kudos-donations'),
			'address' => __('Address', 'kudos-donations'),
			'donations' => __('Donations', 'kudos-donations'),
			'donor_created'=>__('Date', 'kudos-donations')
		];
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since      2.0.0
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
	 * @since      2.0.0
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
	 * @since      2.0.0
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

		return __('Added', 'kudos-donations') . '<br/>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['donor_created']));
	}

	/**
	 * Email column
	 *
	 * @since      2.0.0
	 * @param array $item
	 * @return string
	 */
	function column_email( $item ) {

		$delete_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = sprintf(
			'<a href="mailto: %1$s" />%1$s</a>', $item['email']
		);

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer_id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', sanitize_text_field( $item['customer_id'] ), $delete_nonce, __('Delete', 'kudos-donations') ),
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Address column
	 *
	 * @since      2.0.0
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
	 * @since      2.0.0
	 * @param array $item
	 * @return string
	 */
	function column_donations( $item ) {

//		$donor = new Donor();
//		$donor->get_by(['customer_id' => $item['customer_id']]);
//		$transactions = $donor->get_transactions();

		$transaction = new Transaction();
		$transactions = $transaction->get_all(['customer_id' => $item['customer_id']]);

		if($transactions) {
			$number = count($transactions);
			$total = 0;
			/** @var Transaction $transaction */
			foreach ($transactions as $transaction) {
				if($transaction->fields['status'] === 'paid') {
					$refunds = $transaction->get_refunds();
					if ( $refunds ) {
						$total = $total + $refunds['remaining'];
					} else {
						$total = $total + $transaction->fields['value'];
					}
				}
			}

			return '<a href="'. admin_url('admin.php?page=kudos-transactions&customer_id='. urlencode($item['customer_id']) .'') .'">' . $number . ' ( ' . get_currency_symbol($transactions[0]->fields['currency']) . $total . ' )' . '</a>';
		}

		return false;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since      2.0.0
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
	 * @since      2.0.0
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		switch ($this->current_action()) {

			case 'delete':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				} else {
					self::delete_record('customer_id', sanitize_text_field( $_GET['customer_id'] ) );
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
						self::delete_record('customer_id', $id );
					}
				}
				break;
		}
	}
}