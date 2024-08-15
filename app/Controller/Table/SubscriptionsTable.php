<?php
/**
 * Subscriptions table.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Controller\Table;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;
use WP_List_Table;

class SubscriptionsTable extends WP_List_Table {

	use TableTrait;

	/**
	 * @var MapperService
	 */
	private $mapper;
	/**
	 * @var PaymentService
	 */
	private $payment;

	/**
	 * Class constructor
	 *
	 * @param MapperService  $mapper_service The mapper.
	 * @param PaymentService $payment_service The payment service.
	 */
	public function __construct( MapperService $mapper_service, PaymentService $payment_service ) {

		$this->mapper  = $mapper_service;
		$this->table   = SubscriptionEntity::get_table_name();
		$this->payment = $payment_service;

		$this->search_columns = [
			'name'      => __( 'Name', 'kudos-donations' ),
			'email'     => __( 'Email', 'kudos-donations' ),
			'frequency' => __( 'Frequency', 'kudos-donations' ),
		];

		$this->export_columns = [
			'created'   => __( 'Date', 'kudos-donations' ),
			'name'      => __( 'Name', 'kudos-donations' ),
			'email'     => __( 'Email', 'kudos-donations' ),
			'value'     => __( 'Amount', 'kudos-donations' ),
			'status'    => __( 'Status', 'kudos-donations' ),
			'frequency' => __( 'Frequency', 'kudos-donations' ),
			'years'     => __( 'Years', 'kudos-donations' ),
			'currency'  => __( 'Currency', 'kudos-donations' ),
		];

		parent::__construct(
			[
				'orderBy'  => 'created',
				'singular' => __( 'Subscription', 'kudos-donations' ),
				'plural'   => __( 'Subscriptions', 'kudos-donations' ),
				'ajax'     => false,
			]
		);
	}

	/**
	 * Call this function where the table is to be displayed
	 */
	public function display() {

		$this->views();
		$this->search_box( __( 'Search', 'kudos-donations' ) . ' ' . $this->_args['plural'], 'search_records' );
		parent::display();
	}

	/**
	 * Get the table data
	 */
	public function fetch_table_data(): array {

		global $wpdb;
		$frequency = ( isset( $_GET['frequency'] ) ? sanitize_text_field( $_GET['frequency'] ) : '' );
		$search    = $this->get_search_data();

		// Base data.
		$table      = $this->table;
		$join_table = DonorEntity::get_table_name();
		$query      = "
			SELECT $table.*, $join_table.name, $join_table.email FROM $table
			LEFT JOIN $join_table on $join_table.customer_id = $table.customer_id
		";
		$where      = [];

		// Where clause.
		if ( $frequency ) {
			$where[] = $wpdb->prepare(
				"
				$table.frequency = %s
			",
				$frequency
			);
		}

		if ( $search ) {
			$where[] = $wpdb->prepare(
				"
				${search['field']} = %s
			",
				$search['term']
			);
		}

		$where = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$query = $query . $where;

		return $this->mapper
			->get_repository( SubscriptionEntity::class )
			->get_results( $query );
	}

	/**
	 * Returns a list of columns to include in table
	 */
	public function column_names(): array {
		return [
			'created'         => __( 'Date', 'kudos-donations' ),
			'frequency'       => __( 'Frequency', 'kudos-donations' ),
			'years'           => __( 'Years', 'kudos-donations' ),
			'name'            => __( 'Name', 'kudos-donations' ),
			'email'           => __( 'E-mail', 'kudos-donations' ),
			'value'           => __( 'Amount', 'kudos-donations' ),
			'status'          => __( 'Status', 'kudos-donations' ),
			'subscription_id' => __( 'Subscription Id', 'kudos-donations' ),
		];
	}

	/**
	 * Define which columns are hidden.
	 */
	public function get_hidden_columns(): array {
		return [
			'subscription_id',
		];
	}

	/**
	 * Define the sortable columns.
	 */
	public function get_sortable_columns(): array {
		return [
			'created' => [
				'created',
				false,
			],
			'value'   => [
				'value',
				false,
			],
			'status'  => [
				'status',
				false,
			],
		];
	}

	/**
	 * Process delete, cancel and bulk-delete actions
	 *
	 * @since      2.0.0
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered.
		switch ( $this->current_action() ) {

			case 'cancel':
				// Verify the nonce.
				if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce(
					sanitize_key( $_REQUEST['_wpnonce'] ),
					'bulk-' . $this->_args['singular']
				) ) {
					die();
				}

				if ( isset( $_GET['id'] ) ) {
					$this->cancel_subscription( sanitize_text_field( wp_unslash( $_GET['id'] ) ) );
				}

				break;

			case 'delete':
				// Verify the nonce.
				if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce(
					sanitize_key( $_REQUEST['_wpnonce'] ),
					'bulk-' . $this->_args['singular']
				) ) {
					die();
				}

				if ( isset( $_GET['id'] ) ) {
					self::delete_record(
						'id',
						sanitize_text_field( wp_unslash( $_GET['id'] ) )
					);
				}

				break;

			case 'bulk-delete':
				// Verify the nonce.
				if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce(
					sanitize_key( $_REQUEST['_wpnonce'] ),
					'bulk-' . $this->_args['plural']
				) ) {
					die();
				}

				if ( isset( $_REQUEST['bulk-action'] ) ) {
					$customer_ids = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['bulk-action'] ) );
					foreach ( $customer_ids as $id ) {
						self::delete_record( 'id', sanitize_text_field( $id ) );
					}
				}
				break;
		}
	}

	/**
	 * Cancel a subscription.
	 *
	 * @param string $id subscription row ID.
	 */
	public function cancel_subscription( string $id ): bool {

		$payment_service = $this->payment;
		$mapper          = $this->mapper;
		/** @var SubscriptionEntity $subscription */
		$subscription = $mapper->get_repository( SubscriptionEntity::class )
								->get_one_by(
									[
										'id' => $id,
									]
								);

		return $payment_service->cancel_subscription( $subscription->subscription_id );
	}

	/**
	 * Delete a subscription.
	 *
	 * @param string $column Column name to search.
	 * @param string $id Value to search for.
	 * @return false|int
	 */
	protected function delete_record( string $column, string $id ) {

		return $this->mapper
			->get_repository( SubscriptionEntity::class )
			->delete( $column, $id );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since      2.0.0
	 *
	 * @param array $item Array of results.
	 */
	protected function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Time (date) column
	 *
	 * @since      2.0.0
	 *
	 * @param array $item Array of results.
	 */
	protected function column_created( array $item ): string {

		$title = '<strong>' .
				wp_date(
					get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
					strtotime( $item['created'] )
				) .
				'</strong>';

		$url = add_query_arg(
			[
				'page'     => esc_attr( $_REQUEST['page'] ),
				'id'       => sanitize_text_field( $item['id'] ),
				'_wpnonce' => wp_create_nonce( 'bulk-' . $this->_args['singular'] ),
			]
		);

		$actions = [];
		if ( 'active' === $item['status'] ) {
			$url               = add_query_arg(
				[
					'action' => 'cancel',
				],
				$url
			);
			$actions['cancel'] = sprintf(
				'<a href="%s">%s</a>',
				$url,
				__( 'Cancel', 'kudos-donations' )
			);
		} else {
			$url               = add_query_arg(
				[
					'action' => 'delete',
				],
				$url
			);
			$actions['cancel'] = sprintf(
				'<a href=%s>%s</a>',
				$url,
				__( 'Delete', 'kudos-donations' )
			);
		}

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Subscription frequency column
	 *
	 * @param array $item Array of results.
	 */
	protected function column_frequency( array $item ): string {
		return Utils::get_frequency_name( $item['frequency'] );
	}

	/**
	 * Year column
	 *
	 * @param array $item Array of results.
	 */
	protected function column_years( array $item ): string {

		return ( $item['years'] > 0 ? $item['years'] : __( 'Continuous', 'kudos-donations' ) );
	}

	/**
	 * Name column
	 *
	 * @since   2.0.0
	 *
	 * @param array $item Array of results.
	 */
	protected function column_name( array $item ): ?string {

		$customer_id = $item['customer_id'];

		if ( $customer_id ) {
			return sprintf(
				'<a href="%s" />%s</a>',
				admin_url( sprintf( 'admin.php?page=kudos-donors&search-field=customer_id&s=%s', $customer_id ) ),
				$item['name']
			);
		}

		return $item['name'];
	}

	/**
	 * Email column
	 *
	 * @param array $item Array of results.
	 */
	protected function column_email( array $item ): string {
		return sprintf(
			'<a href="mailto: %1$s" />%1$s</a>',
			$item['email']
		);
	}

	/**
	 * Value (amount) column
	 *
	 * @param array $item Array of results.
	 */
	protected function column_value( array $item ): string {

		$currency = ! empty( $item['currency'] ) ? Utils::get_currency_symbol( $item['currency'] ) : '';

		return $currency . ' ' . number_format_i18n( $item['value'], 2 );
	}

	/**
	 * Payment status column
	 *
	 * @param array $item Array of results.
	 */
	protected function column_status( array $item ): string {

		switch ( $item['status'] ) {
			case 'active':
				$status = __( 'Active', 'kudos-donations' );
				break;
			case 'cancelled':
				$status = __( 'Cancelled', 'kudos-donations' );
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
	 *
	 * @return array|string[]
	 */
	protected function get_bulk_actions(): array {
		return [
			'bulk-delete' => __( 'Delete', 'kudos-donations' ),
		];
	}

	/**
	 * Gets view data
	 *
	 * @since      2.0.0
	 */
	protected function get_views(): array {
		$views   = [];
		$current = ( ! empty( $_GET['frequency'] ) ? sanitize_text_field( $_GET['frequency'] ) : 'all' );

		// All link.
		$count        = \count( $this->mapper->get_all_by() );
		$class        = ( 'all' === $current && empty( $_REQUEST['s'] ) ? ' class="current"' : '' );
		$all_url      = remove_query_arg( 'frequency' );
		$views['all'] = "<a href='$all_url' $class >" . __( 'All', 'kudos-donations' ) . " ($count)</a>";

		// Yearly link.
		$count           = \count( $this->mapper->get_all_by( [ 'frequency' => '12 months' ] ) );
		$class           = ( '12 months' === $current ? ' class="current"' : '' );
		$yearly_url      = add_query_arg( 'frequency', '12 months' );
		$views['yearly'] = "<a href='$yearly_url' $class >" . __( 'Yearly', 'kudos-donations' ) . " ($count)</a>";

		// Quarterly link.
		$count              = \count( $this->mapper->get_all_by( [ 'frequency' => '3 months' ] ) );
		$class              = ( '3 months' === $current ? ' class="current"' : '' );
		$yearly_url         = add_query_arg( 'frequency', '3 months' );
		$views['quarterly'] = "<a href='$yearly_url' $class >" . __(
			'Quarterly',
			'kudos-donations'
		) . " ($count)</a>";

		// Monthly link.
		$count            = \count( $this->mapper->get_all_by( [ 'frequency' => '1 month' ] ) );
		$class            = ( '1 month' === $current ? ' class="current"' : '' );
		$monthly_url      = add_query_arg( 'frequency', '1 month' );
		$views['monthly'] = "<a href='$monthly_url' $class >" . __(
			'Monthly',
			'kudos-donations'
		) . " ($count)</a>";

		return $views;
	}
}
