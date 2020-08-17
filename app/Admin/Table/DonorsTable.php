<?php

namespace Kudos\Admin\Table;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use WP_List_Table;

class DonorsTable extends WP_List_Table {

	use TableTrait;

	/**
	 * @var MapperService
	 */
	private $mapper;

	/**
	 * Class constructor
	 *
	 * @since   2.0.0
	 */
	public function __construct() {

		$this->mapper = new MapperService(DonorEntity::class);
		$this->table = $this->mapper->get_table_name();

		$this->export_columns = [
			'name' => __('Name', 'kudos-donations'),
			'email' => __('Email', 'kudos-donations'),
			'street' => __('Street', 'kudos-donations'),
			'postcode' => __('Postcode', 'kudos-donations'),
			'city' => __('City', 'kudos-donations'),
			'country' => __('Country', 'kudos-donations'),
		];

		parent::__construct( [
			'orderBy'  => 'created',
			'singular' => __( 'Donor', 'kudos-donations' ),
			'plural'   => __( 'Donors', 'kudos-donations' ),
			'ajax'     => false
		] );

	}

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
	 * Get the table data
	 *
	 * @return array
	 * @since   2.0.0
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

		$table = $this->table;

		return $wpdb->get_results("
			SELECT *
			FROM $table
			$search_custom_vars
		", ARRAY_A);
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
			'created'=>__('Date', 'kudos-donations')
		];
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 * @since   2.0.0
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
	 * @return array
	 * @since   2.0.0
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
	 * @param array $item
	 * @return string
	 * @since   2.0.0
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />', $item['customer_id']
		);
	}

	/**
	 * Time (date) column
	 *
	 * @param array $item an array of DB data
	 * @return string
	 * @since   1.0.0
	 */
	function column_created( $item ) {

		return __('Added', 'kudos-donations') . '<br/>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created']));
	}

	/**
	 * Email column
	 *
	 * @param array $item
	 * @return string
	 * @since   2.0.0
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
	 * @param array $item
	 * @return string
	 * @since   2.0.0
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
	 * @param array $item
	 * @return string
	 * @since   2.0.0
	 */
	function column_donations( $item ) {

		$mapper = new MapperService(TransactionEntity::class);
		$transactions = $mapper->get_all_by([ 'customer_id' => $item['customer_id']], OBJECT);

		if($transactions) {
			$number = count($transactions);
			$total = 0;
			/** @var TransactionEntity $transaction */
			foreach ($transactions as $transaction) {
				if($transaction->status === 'paid') {
					$refunds = $transaction->get_refund();
					if ( $refunds ) {
						$total = $total + $refunds['remaining'];
					} else {
						$total = $total + $transaction->value;
					}
				}
			}

			return '<a href="'. admin_url('admin.php?page=kudos-transactions&s='. urlencode($item['email']) .'') .'">' . $number . ' ( ' . Utils::get_currency_symbol($transactions[0]->currency) . $total . ' )' . '</a>';
		}

		return false;
	}

	/**
	 * Delete a donor.
	 *
	 * @param $column
	 * @param int $customer_id
	 * @return false|int
	 * @since   1.0.0
	 */
	protected function delete_record( $column, $customer_id ) {

		return $this->mapper->delete($column, $customer_id);
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array|string
	 * @since   2.0.0
	 */
	function get_bulk_actions() {
		return [
			'bulk-delete'   => __('Delete', 'kudos-donations'),
		];
	}

	/**
	 * Process cancel and bulk-cancel actions
	 *
	 * @since   2.0.0
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