<?php

namespace Kudos\Controller\Table;

use Exception;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Campaign;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use WP_List_Table;

class TransactionsTable extends WP_List_Table {

	use TableTrait;

	/**
	 * @var MapperService
	 */
	private $mapper;

	/**
	 * Class constructor
	 */
	public function __construct( MapperService $mapper_service ) {

		$this->mapper = $mapper_service;
		$this->table = TransactionEntity::get_table_name();

		$this->search_columns = [
			'name'        => __( 'Name', 'kudos-donations' ),
			'email'       => __( 'Email', 'kudos-donations' ),
			'campaign_id' => __( 'Campaign', 'kudos-donations' ),
			'order_id'    => __( 'Order ID', 'kudos-donations' ),
			'customer_id' => __( 'Customer ID', 'kudos-donations' ),
		];

		$this->export_columns = [
			'created'       => __( 'Transaction created', 'kudos-donations' ),
			'currency'      => __( 'Currency', 'kudos-donations' ),
			'value'         => __( 'Amount', 'kudos-donations' ),
			'refunds'       => __( 'Refunded', 'kudos-donations' ),
			'status'        => __( 'Status', 'kudos-donations' ),
			'name'          => __( 'Name', 'kudos-donations' ),
			'email'         => __( 'Email', 'kudos-donations' ),
			'method'        => __( 'Method', 'kudos-donations' ),
			'mode'          => __( 'Mode', 'kudos-donations' ),
			'sequence_type' => __( 'Type', 'kudos-donations' ),
		];

		parent::__construct(
			[
				'orderBy'  => 'created',
				'singular' => __( 'Transaction', 'kudos-donations' ),
				'plural'   => __( 'Transactions', 'kudos-donations' ),
				'ajax'     => false,
			]
		);

	}

	/**
	 * Call this function where the table is to be displayed
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
	 */
	public function fetch_table_data(): array {

		$view   = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$search = $this->get_search_data();

		// Base data
		$table      = $this->table;
		$join_table = DonorEntity::get_table_name();
		$query      = "
			SELECT $table.*, $join_table.name, $join_table.email, $join_table.customer_id $join_table
			FROM $table
			LEFT JOIN $join_table on $join_table.customer_id = $table.customer_id
		";

		// Having clause
		if ( $view ) {
			global $wpdb;
			$having[] = $wpdb->prepare( "
				$table.status = %s
			",
				$view );
		}

		if ( $search ) {
			global $wpdb;
			$having[] = $wpdb->prepare( "
				${search['field']} LIKE %s
			",
				$search['term'] );
		}

		$having = ! empty( $having ) ? 'HAVING ' . implode( " AND ", $having ) : '';
		$query  = $query . $having;

		return $this->mapper
			->get_repository(TransactionEntity::class)
			->get_results( $query );

	}

	/**
	 * Returns a list of columns to include in table
	 *
	 * @return array
	 */
	public function column_names(): array {

		return [
			'created'        => __( 'Date', 'kudos-donations' ),
			'name'           => __( 'Name', 'kudos-donations' ),
			'email'          => __( 'E-mail', 'kudos-donations' ),
			'value'          => __( 'Amount', 'kudos-donations' ),
			'type'           => __( 'Type', 'kudos-donations' ),
			'status'         => __( 'Status', 'kudos-donations' ),
			'order_id'       => __( 'Order ID', 'kudos-donations' ),
			'transaction_id' => __( 'Transaction Id', 'kudos-donations' ),
			'campaign_id'    => __( 'Campaign', 'kudos-donations' ),
			'message'        => __( 'Message', 'kudos-donations' ),
			'customer_id'    => __( 'Customer ID', 'kudos-donations' ),
		];

	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 */
	public function get_hidden_columns(): array {
		return [
			'transaction_id',
			'customer_id',
			'order_id',
		];
	}

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array {

		return [
			'created'     => [
				'created',
				false,
			],
			'value'       => [
				'value',
				false,
			],
			'campaign_id' => [
				'campaign_id',
				false,
			],
		];

	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 */
	function column_cb( $item ): string {

		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />',
			$item['id']
		);

	}

	/**
	 * Process delete and bulk-delete actions
	 *
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered.
		switch ( $this->current_action() ) {

			case 'delete':
				// Verify the nonce.
				if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ),
						'bulk-' . $this->_args['singular'] ) ) {
					die();
				}

				if ( isset( $_GET['id'] ) ) {
					self::delete_record( 'id', sanitize_text_field( wp_unslash( $_GET['id'] ) ) );
				}

				break;

			case 'bulk-delete':
				// Verify the nonce.
				if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ),
						'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if ( isset( $_REQUEST['bulk-action'] ) ) {
					$order_ids = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['bulk-action'] ) );
					foreach ( $order_ids as $id ) {
						self::delete_record( 'id', sanitize_text_field( $id ) );
					}
				}
				break;
		}

	}

	/**
	 * Delete a transaction.
	 *
	 * @param string $column Column name to search.
	 * @param string $id Value to search for.
	 *
	 * @return false|int
	 */
	protected function delete_record( string $column, string $id ) {

		return $this->mapper
			->get_repository(TransactionEntity::class)
			->delete( $column, $id );

	}

	/**
	 * Time (date) column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 */
	protected function column_created( array $item ): string {

		$title = '<strong>' .
		         wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			         strtotime( $item['created'] ) ) .
		         '</strong>';

		$url = add_query_arg( [
			'page'     => esc_attr( $_REQUEST['page'] ),
			'action'   => 'delete',
			'id'       => $item['id'],
			'_wpnonce' => wp_create_nonce( 'bulk-' . $this->_args['singular'] ),
		] );

		$actions = apply_filters(
			TransactionEntity::get_table_name( false ) . '_actions',
			[
				'delete' => sprintf(
					'<a href="%s">%s</a>',
					$url,
					__( 'Delete', 'kudos-donations' )
				),
			],
			$item['id']
		);

		return $title . $this->row_actions( $actions );

	}

	/**
	 * Name column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string|null
	 */
	protected function column_name( array $item ): ?string {

		$customer_id = $item['customer_id'] ?? null;

		if ( $customer_id ) {
			return sprintf(
				"<a href='%s' />%s</a>",
				admin_url( sprintf( 'admin.php?page=kudos-donors&search-field=customer_id&s=%s', $customer_id ) ),
				$item['name']
			);
		}

		return $item['name'] ?? '';

	}

	/**
	 * Email column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 */
	protected function column_email( array $item ): string {

		if ( isset( $item['email'] ) ) {
			return sprintf(
				'<a href="mailto: %1$s" />%1$s</a>',
				$item['email']
			);
		}

		return '';

	}

	/**
	 * Value (amount) column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string|void
	 */
	protected function column_value( array $item ): string {

		switch ( $item['method'] ) {
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

		$currency = ! empty( $item['currency'] ) ? Utils::get_currency_symbol( $item['currency'] ) : '';

		$value = $item['value'];

		if ( $item['refunds'] ) {
			$refund = json_decode( $item['refunds'] );
			$value  = json_last_error() == JSON_ERROR_NONE ? $refund->remaining : '';
		}

		return '<i title="' . $item['method'] . '" class="' . $icon . '"></i> ' .
		       $currency . ' ' . number_format_i18n( $value, 2 );

	}

	/**
	 * Payment type column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string|void
	 */
	protected function column_type( array $item ): string {

		return Utils::get_sequence_type( $item['sequence_type'] );

	}

	/**
	 * Payment status column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string|void
	 */
	protected function column_status( array $item ): string {

		switch ( $item['status'] ) {
			case 'paid':
				$status = __( 'Paid', 'kudos-donations' );
				break;
			case 'open':
				$status = __( 'Open', 'kudos-donations' );
				break;
			case 'expired':
				$status = __( 'Expired', 'kudos-donations' );
				break;
			case 'canceled':
				$status = __( 'Cancelled', 'kudos-donations' );
				break;
			case 'failed':
				$status = __( 'Failed', 'kudos-donations' );
				break;
			default:
				$status = __( 'Unknown', 'kudos-donations' );
		}

		if ( $item['refunds'] ) {
			$status .= ' (' . __( 'Refunded', 'kudos-donations' ) . ')';
		}

		return apply_filters( 'kudos_transactions_column_status', $status, $item['order_id'] );

	}

	/**
	 * Order Id column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string|void
	 */
	protected function column_order_id( array $item ): string {

		return $item['order_id'] . ( 'test' === $item['mode'] ? ' (' . $item['mode'] . ')' : '' );

	}

	/**
	 * Return campaign label as a search link
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 */
	protected function column_campaign_id( array $item ): string {

		if(!$item['campaign_id']) return '';

		try {
			$campaign = Campaign::get_campaign( $item['campaign_id'] );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		if ( $campaign ) {
			return sprintf(
				'<a href=%1$s>%2$s</a>',
				sprintf( admin_url( 'admin.php?page=kudos-campaigns&search-field=name&s=%s' ),
					rawurlencode( $campaign['name'] ) ),
				$campaign['name']
			);
		}

		return $item['campaign_id'] ?? '';

	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	protected function get_bulk_actions(): array {

		return [
			'bulk-delete' => __( 'Delete', 'kudos-donations' ),
		];

	}

	/**
	 * Gets view data
	 *
	 * @return array
	 */
	protected function get_views(): array {

		$views   = [];
		$current = ( ! empty( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all' );

		// Remove search query from current url.
		$url = remove_query_arg( 's' );

		// All link.
		$count        = count( $this->mapper->get_all_by() );
		$class        = ( 'all' === $current && empty( $_REQUEST['s'] ) ? ' class="current"' : '' );
		$all_url      = remove_query_arg( [ 'status' ], $url );
		$views['all'] = "<a href='$all_url' $class >" . __( 'All', 'kudos-donations' ) . " ($count)</a>";

		// Paid link.
		$count         = count( $this->mapper->get_all_by( [ 'status' => 'paid' ] ) );
		$paid_url      = add_query_arg( 'status', 'paid', $url );
		$class         = ( 'paid' === $current ? ' class="current"' : '' );
		$views['paid'] = "<a href='$paid_url' $class >" . __( 'Paid', 'kudos-donations' ) . " ($count)</a>";

		// Open link.
		$count         = count( $this->mapper->get_all_by( [ 'status' => 'open' ] ) );
		$open_url      = add_query_arg( 'status', 'open', $url );
		$class         = ( 'open' === $current ? ' class="current"' : '' );
		$views['open'] = "<a href='$open_url' $class >" . __( 'Open', 'kudos-donations' ) . " ($count)</a>";

		// Canceled link.
		$count             = count( $this->mapper->get_all_by( [ 'status' => 'canceled' ] ) );
		$canceled_url      = add_query_arg( 'status', 'canceled', $url );
		$class             = ( 'canceled' === $current ? ' class="current"' : '' );
		$views['canceled'] = "<a href='$canceled_url' $class >" . __( 'Cancelled',
				'kudos-donations' ) . " ($count)</a>";

		// Canceled link.
		$count            = count( $this->mapper->get_all_by( [ 'status' => 'expired' ] ) );
		$expired_url      = add_query_arg( 'status', 'expired', $url );
		$class            = ( 'expired' === $current ? ' class="current"' : '' );
		$views['expired'] = "<a href='$expired_url' $class >" . __( 'Expired',
				'kudos-donations' ) . " ($count)</a>";

		return $views;

	}
}
