<?php

namespace Kudos\Admin\Table;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use WP_List_Table;

class CampaignsTable extends WP_List_Table {

	use TableTrait;

	/**
	 * @var MapperService
	 */
	private $mapper;

	/**
	 * Class constructor
	 *
	 * @since   2.0.4
	 */
	public function __construct() {

		$this->mapper = new MapperService( TransactionEntity::class );
		$this->table  = $this->mapper->get_table_name();

		$this->export_columns = [
			'date'         => __( 'Name', 'kudos-donations' ),
			'label'        => __( 'Email', 'kudos-donations' ),
			'transactions' => __( 'Street', 'kudos-donations' ),
			'total'        => __( 'Total', 'kudos-donations' ),
		];

		parent::__construct( [
			'orderBy'  => 'date',
			'singular' => __( 'Campaign', 'kudos-donations' ),
			'plural'   => __( 'Campaigns', 'kudos-donations' ),
			'ajax'     => false,
		] );

	}

	/**
	 * Call this function where the table is to be displayed
	 *
	 * @since      2.0.4
	 */
	public function display() {

		$this->views();
		$this->search_box( __( 'Search' ) . ' ' . $this->_args['plural'], 'search_records' );
		parent::display();

	}

	/**
	 * Get the table data
	 *
	 * @return array
	 * @since   2.0.4
	 */
	public function fetch_table_data() {

		$mapper    = $this->mapper;
		$campaigns = Settings::get_setting( 'campaign_labels' );

		// Add search query if exist
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search    = esc_sql( $_REQUEST['s'] );
			$campaigns = array_filter( $campaigns,
				function ( $value ) use ( $search ) {
					return in_array( $search, $value );
				} );
		}

		foreach ( $campaigns as $key => $campaign ) {
			$label = $campaign['label'];

			$transactions = $mapper->get_all_by( [ 'campaign_label' => $label ], OBJECT );

			$campaigns[ $key ]['transactions'] = 0;
			$campaigns[ $key ]['total']        = 0;
			if ( $transactions ) {
				$total = 0;
				foreach ( $transactions as $transaction ) {
					if ( $transaction->status === 'paid' ) {
						$refunds = $transaction->get_refund();
						if ( $refunds ) {
							$total = $total + $refunds['remaining'];
						} else {
							$total = $total + $transaction->value;
						}
					}
				}
				$campaigns[ $key ]['transactions'] = count( $transactions );
				$campaigns[ $key ]['currency']     = $transactions[0]->currency;
				$campaigns[ $key ]['total']        = $total;
			}
		}

		return $campaigns;
	}

	/**
	 * Returns a list of columns to include in table
	 *
	 * @return array
	 * @since   2.0.4
	 */
	public function column_names() {
		return [
			'date'         => __( 'Date', 'kudos-donations' ),
			'label'        => __( 'Label', 'kudos-donations' ),
			'transactions' => __( 'Transactions', 'kudos-donations' ),
			'total'        => __( 'Total', 'kudos-donations' ),
		];
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 * @since   2.0.4
	 */
	public function get_hidden_columns() {
		return [
			'subscription_id',
			'id',
		];
	}

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 * @since   2.0.4
	 */
	public function get_sortable_columns() {
		return [
			'date' => [
				'date',
				false,
			],
		];
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 * @since   2.0.4
	 */
//	function column_cb( $item ) {
//		return sprintf(
//			'<input type="checkbox" name="bulk-action[]" value="%s" />',
//			$item['campaign_label']
//		);
//	}

	/**
	 * Time (date) column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 * @since   2.0.4
	 */
	function column_date( array $item ) {

		return __( 'Added',
				'kudos-donations' ) . '<br/>' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				strtotime( $item['date'] ) );
	}

	/**
	 * Total column
	 *
	 * @param array $item
	 *
	 * @return string
	 * @since 2.0.4
	 */
	function column_total( array $item ) {
		$currency = ! empty( $item['currency'] ) ? Utils::get_currency_symbol( $item['currency'] ) : '';
		$total    = $item['total'];

		return $currency . ' ' . number_format_i18n( $total, 2 );
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array|string
	 * @since   2.0.4
	 */
	function get_bulk_actions() {
		return [
			'bulk-delete' => __( 'Delete', 'kudos-donations' ),
		];
	}

	/**
	 * Process cancel and bulk-cancel actions
	 *
	 * @since   2.0.4
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		switch ( $this->current_action() ) {

			case 'delete':

				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['singular'] ) ) {
					die();
				}

				self::delete_record( 'customer_id', intval( $_GET['customer_id'] ) );
				break;

			case 'bulk-delete':

				// In our file that handles the request, verify the nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if ( isset( $_REQUEST['bulk-action'] ) ) {
					$cancel_ids = esc_sql( $_REQUEST['bulk-action'] );
					foreach ( $cancel_ids as $id ) {
						self::delete_record( 'customer_id', (int) $id );
					}
				}
				break;
		}
	}

	/**
	 * Delete a donor.
	 *
	 * @param $column
	 * @param int $customer_id
	 *
	 * @return false|int
	 * @since   2.0.4
	 */
	protected function delete_record( $column, int $customer_id ) {

		return $this->mapper->delete( $column, $customer_id );

	}
}