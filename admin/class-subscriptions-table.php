<?php

namespace Kudos;

class Subscriptions_Table extends Table_Object {

	/**
	 * Class constructor
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		global $wpdb;

		parent::__construct( [
			'table'    => $wpdb->prefix . Kudos_Subscription::TABLE,
			'singular' => __( 'Subscription', 'kudos-donations' ), //singular name of the listed records
			'plural'   => __( 'Subscriptions', 'kudos-donations' ), //plural name of the listed records
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
					'frequency' => !empty($_REQUEST['frequency']) ? esc_attr($_REQUEST['frequency']) : '',
					'_wpnonce' => $export_nonce,
					'export_subscriptions' => ''

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

		$subscription = new Kudos_Subscription();
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
				case 'time':
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
	 * Columns to show
	 *
	 * @since      1.0.0
	 * @return array
	 */
	public function get_columns() {
		return $columns= [
			'cb' => '<input type="checkbox" />',
			'time'=>__('Date', 'kudos-donations'),
			'frequency'=>__('Frequency', 'kudos-donations'),
			'email'=>__('E-mail', 'kudos-donations'),
			'value'=>__('Amount', 'kudos-donations'),
			'status'=>__('Status', 'kudos-donations'),
			'subscription_id'=>__('Subscription Id', 'kudos-donations'),
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
		$current = ( !empty($_GET['frequency']) ? sanitize_text_field($_GET['frequency']) : 'all');

		//All link
		$count = count($this->count_records());
		$class = ($current == 'all' ? ' class="current"' :'');
		$all_url = remove_query_arg('frequency');
		$views['all'] = "<a href='{$all_url }' {$class} >". __('All', 'kudos-donations') . " ($count)</a>";

		//Yearly link
		$count = count($this->count_records('frequency', '12 months'));
		$yearly_url = add_query_arg('frequency','12 months');
		$class = ($current == '12 months' ? ' class="current"' :'');
		$views['test'] = "<a href='{$yearly_url}' {$class} >". __('12 months', 'kudos-donations') ." ($count)</a>";

		//Monthly link
		$count = count($this->count_records('frequency', '1 month'));
		$monthly_url = add_query_arg('frequency','1 month');
		$class = ($current == '1 month' ? ' class="current"' :'');
		$views['live'] = "<a href='{$monthly_url}' {$class} >". __('1 Month', 'kudos-donations') ." ($count)</a>";
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
			'subscription_id'
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
			'<input type="checkbox" name="bulk-action[]" value="%s" />', $item['subscription_id']
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
			'cancel' => sprintf( '<a href="?page=%s&action=%s&subscription_id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'cancel', sanitize_text_field( $item['subscription_id'] ), $delete_nonce, __('Cancel', 'kudos-donations') ),
		];

//		$subscription_id = sanitize_text_field($item["subscription_id"]);

//		$actions = [
//			'cancel' => '
//				<a href="#TB_inline?&inlineId=cancel-modal-'.$subscription_id.'&width=chicken&height=100" title="MODAL WINDOW TITLE" class="thickbox">'. __('Cancel', 'kudos-donations') .'</a>
//				<div id="cancel-modal-'.$subscription_id.'" style="display:none; width: 100px">
//				    <p>Are you sure?</p>
//				    '. sprintf( '<a href="?page=%s&action=%s&subscription_id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'cancel', $subscription_id, $delete_nonce, __('Cancel', 'kudos-donations') ) .'
//				</div>
//			',
//		];

		return $title . $this->row_actions( $actions );
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
	 * Value (amount) column
	 *
	 * @since      1.0.0
	 * @param array $item
	 * @return string|void
	 */
	function column_value($item)
	{

		$currency = !empty($item['currency']) ? get_currency_symbol($item['currency']) : '';

		return $currency . ' ' . number_format_i18n($item['value'], 2);

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
			case 'active':
				$status = __('Active', 'kudos-donations');
				break;
			default:
				$status = $item['status'];
		}

		$frequency = $item['frequency'] === 'test' ? ' ('. $item['frequency'] .')' : '';

		return $status . ' ' . $frequency;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since      1.0.0
	 * @return array|string[]
	 */
	function get_bulk_actions() {
		return [
			'bulk-cancel'   => __('Cancel', 'kudos-donations'),
		];
	}

	/**
	 * Cancel a subscription.
	 *
	 * @since      1.0.0
	 * @param int $subscription_id order ID
	 */
	public static function cancel_subscription( $subscription_id ) {

		$mollie = new Kudos_Mollie();
		$subscription = new Kudos_Subscription();
		$customer = $subscription->get_by(['subscription_id' => $subscription_id]);

		if(empty($customer)) {
			return;
		}

		$customer_id = $customer->customer_id;

		$subscription = $mollie->cancel_subscription($customer_id, $subscription_id);

		if($subscription) {
			global $wpdb;
			$wpdb->delete(
				$table = $wpdb->prefix . Kudos_Subscription::TABLE,
				[ 'subscription_id' => $subscription_id ]
			);
		}
	}

	/**
	 * Process cancel and bulk-cancel actions
	 *
	 * @since      1.0.0
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
		}
	}
}