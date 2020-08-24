<?php

namespace Kudos\Admin\Table;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use Kudos\Service\MollieService;
use WP_List_Table;

class SubscriptionsTable extends WP_List_Table {

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

		$this->mapper = new MapperService(SubscriptionEntity::class);
		$this->table = SubscriptionEntity::TABLE;

		$this->export_columns = [
				'created' => __('Date', 'kudos-donations'),
				'name' => __('Name', 'kudos-donations'),
				'email' => __('Email', 'kudos-donations'),
				'value' => __('Amount', 'kudos-donations'),
				'status' => __('Status', 'kudos-donations'),
				'frequency' => __('Frequency', 'kudos-donations'),
				'years' => __('Years', 'kudos-donations'),
				'currency' => __('Currency', 'kudos-donations'),
		];

		parent::__construct( [
			'orderBy'  => 'created',
			'singular' => __( 'Subscription', 'kudos-donations' ),
			'plural'   => __( 'Subscriptions', 'kudos-donations' ),
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
	 * @since      2.0.0
	 */
	public function fetch_table_data() {

		global $wpdb;
		$search_custom_vars = null;
		$frequency = (!empty($_GET['frequency']) ? sanitize_text_field($_GET['frequency']) : '');

		// Add frequency if exist
		if($frequency) {
			$search_custom_vars = $wpdb->prepare(
				"WHERE frequency = %s", esc_sql($frequency)
			);
		}

		// Add search query if exist
		if(!empty($_REQUEST['s'])) {
			$search = esc_sql($_REQUEST['s']);
			$search_custom_vars .= $wpdb->prepare(
				($search_custom_vars ? " AND" : " WHERE") . " (`email` LIKE '%%%s%%') OR (`name` LIKE '%%%s%')",
				$search, $search
			);
		}

		$table = $wpdb->prefix . $this->table;
		$join_table = DonorEntity::getTableName();

		$search_custom_vars = " LEFT JOIN $join_table on $join_table.customer_id = $table.customer_id " . $search_custom_vars;

		return $wpdb->get_results("
			SELECT $table.*, $join_table.name, $join_table.email
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
			'created'=>__('Date', 'kudos-donations'),
			'frequency'=>__('Frequency', 'kudos-donations'),
			'years' => __('Years', 'kudos-donations'),
			'name' => __('Name', 'kudos-donations'),
			'email'=>__('E-mail', 'kudos-donations'),
			'value'=>__('Amount', 'kudos-donations'),
			'status'=>__('Status', 'kudos-donations'),
			'subscription_id'=>__('Subscription Id', 'kudos-donations'),
		];
	}

	/**
	 * Gets view data
	 *
	 * @since      2.0.0
	 * @return array
	 */
	protected function get_views() {
		$views = [];
		$current = ( !empty($_GET['frequency']) ? sanitize_text_field($_GET['frequency']) : 'all');

		//All link
		$count = count($this->count_records());
		$class = ($current == 'all' && empty($_REQUEST['s']) ? ' class="current"' :'');
		$all_url = remove_query_arg('frequency');
		$views['all'] = "<a href='{$all_url }' {$class} >". __('All', 'kudos-donations') . " ($count)</a>";

		//Yearly link
		$count = count($this->count_records('frequency', '12 months'));
		$yearly_url = add_query_arg('frequency','12 months');
		$class = ($current == '12 months' ? ' class="current"' :'');
		$views['yearly'] = "<a href='{$yearly_url}' {$class} >". __('Yearly', 'kudos-donations') ." ($count)</a>";

		//Quarterly link
		$count = count($this->count_records('frequency', '3 months'));
		$yearly_url = add_query_arg('frequency','3 months');
		$class = ($current == '3 months' ? ' class="current"' :'');
		$views['quarterly'] = "<a href='{$yearly_url}' {$class} >". __('Quarterly', 'kudos-donations') ." ($count)</a>";

		//Monthly link
		$count = count($this->count_records('frequency', '1 month'));
		$monthly_url = add_query_arg('frequency','1 month');
		$class = ($current == '1 month' ? ' class="current"' :'');
		$views['monthly'] = "<a href='{$monthly_url}' {$class} >". __('Monthly', 'kudos-donations') ." ($count)</a>";
		return $views;

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
			'subscription_id'
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
			'created' => [
				'created',
				false
			],
			'value' => [
				'value',
				false
			],
			'status' => [
				'status',
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
			'<input type="checkbox" name="bulk-action[]" value="%s" />', $item['subscription_id']
		);
	}

	/**
	 * Time (date) column
	 *
	 * @since      2.0.0
	 * @param array $item an array of DB data
	 * @return string
	 */
	function column_created( $item ) {

		$action_nonce = wp_create_nonce( 'bulk-' . $this->_args['singular'] );

		$title = '<strong>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created'])) . '</strong>';

		$actions = [];
		if($item['status'] === 'active') {
			$actions['cancel'] = sprintf( '<a href="?page=%s&action=%s&subscription_id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'cancel', sanitize_text_field( $item['subscription_id'] ), $action_nonce, __('Cancel', 'kudos-donations') );
		} else {
			$actions['delete'] = sprintf( '<a href="?page=%s&action=%s&subscription_id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', sanitize_text_field( $item['subscription_id'] ), $action_nonce, __('Delete', 'kudos-donations') );
		}

		return $title . $this->row_actions( $actions );
	}

	/**
	 * @since      2.0.0
	 * @param $item
	 * @return string|void
	 */
	function column_frequency($item) {
		return Utils::get_frequency_name($item['frequency']);
	}

	/**
	 * @since      2.0.0
	 * @param $item
	 * @return string|void
	 */
	function column_years($item) {

		return ($item['years'] > 0 ? $item['years'] : __('Continuous', 'kudos-donations'));
	}

	/**
	 * Name column
	 *
	 * @param $item
	 * @return string|null
	 * @since   2.0.0
	 */
	function column_name($item) {

		$email = $item['email'];

		if($email) {
			return sprintf(
				"<a href='%s' />%s</a>", admin_url(sprintf("admin.php?page=kudos-donors&s=%s", $email)), $item['name']
			);
		}

		return $item['name'];

	}

	/**
	 * Email column
	 *
	 * @since      2.0.0
	 * @param array $item
	 * @return string
	 */
	function column_email( $item ) {
		return sprintf(
			'<a href="mailto: %1$s" />%1$s</a>', $item['email']
		);
	}

	/**
	 * Value (amount) column
	 *
	 * @since      2.0.0
	 * @param array $item
	 * @return string|void
	 */
	function column_value($item)
	{

		$currency = !empty($item['currency']) ? Utils::get_currency_symbol($item['currency']) : '';

		return $currency . ' ' . number_format_i18n($item['value'], 2);

	}

	/**
	 * Payment status column
	 *
	 * @since      2.0.0
	 * @param array $item
	 * @return string|void
	 */
	function column_status($item)
	{

		switch ($item['status']) {
			case 'active':
				$status = __('Active', 'kudos-donations');
				break;
			case 'cancelled':
				$status = __('Cancelled', 'kudos-donations');
				break;
			default:
				$status = $item['status'];
		}

		return $status;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since      2.0.0
	 * @return array|string[]
	 */
	function get_bulk_actions() {
		return [
			'bulk-cancel'   => __('Cancel', 'kudos-donations'),
			'bulk-delete'   => __('Delete', 'kudos-donations')
		];
	}

	/**
	 * Cancel a subscription.
	 *
	 * @param int $subscription_id order ID
	 * @return bool
	 * @since      2.0.0
	 */
	public static function cancel_subscription( $subscription_id ) {

		$kudos_mollie = new MollieService();
		if($kudos_mollie->cancel_subscription($subscription_id)) {
			return true;
		}
		return false;
	}

	/**
	 * Delete a subscription.
	 *
	 * @param $column
	 * @param int $subscription_id
	 * @return false|int
	 * @since   1.0.0
	 */
	protected function delete_record( $column, $subscription_id ) {

		return $this->mapper->delete($column, $subscription_id);

	}

	/**
	 * Process cancel and bulk-cancel actions
	 *
	 * @since      2.0.0
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		switch ($this->current_action()) {

			case 'cancel':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				} else {
					self::cancel_subscription( sanitize_text_field( $_GET['subscription_id'] ) );
				}
				break;

			case 'delete':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				} else {
					self::delete_record('subscription_id', sanitize_text_field( $_GET['subscription_id'] ) );
				}
				break;

			case 'bulk-cancel':
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

			case 'bulk-delete':
				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if(isset($_REQUEST['bulk-action'])) {
					$cancel_ids = esc_sql( $_REQUEST['bulk-action']);
					foreach ( $cancel_ids as $id ) {
						self::delete_record('subscription_id', $id );
					}
				}
				break;
		}
	}
}