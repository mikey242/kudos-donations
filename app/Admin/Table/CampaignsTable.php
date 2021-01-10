<?php

namespace Kudos\Admin\Table;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Campaigns;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use WP_List_Table;

class CampaignsTable extends WP_List_Table {

	use TableTrait;

	/**
	 * Instance of the MapperService.
	 *
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

		$this->search_columns = [
			'label' => __( 'Label', 'kudos-donations' ),
		];

		$this->export_columns = [
			'label'        => __( 'Email', 'kudos-donations' ),
			'transactions' => __( 'Street', 'kudos-donations' ),
			'total'        => __( 'Total', 'kudos-donations' ),
		];

		parent::__construct(
			[
				'orderBy'  => 'slug',
				'singular' => __( 'Campaign', 'kudos-donations' ),
				'plural'   => __( 'Campaigns', 'kudos-donations' ),
				'ajax'     => false,
			]
		);

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
	public function fetch_table_data(): array {

		$mapper = $this->mapper;
		$search = $this->get_search_data();

		$campaigns = Settings::get_setting( 'campaigns' );
		if ( ! $campaigns ) {
			return [];
		}

		// Add search query if exist.
		if ( $search ) {
			$campaigns = array_filter(
				$campaigns,
				function ( $value ) use ( $search ) {
					return $value[ $search['field'] ] === $search['term'];
				}
			);
		}

		foreach ( $campaigns as $key => $campaign ) {
			$id = $campaign['id'];

			$transactions = $mapper->get_all_by( [ 'campaign_label' => $id ] );

//			$campaigns[ $key ]['date'] = date("r",hexdec(substr($id,3,8)));
			$campaigns[ $key ]['transactions'] = 0;
			$campaigns[ $key ]['total']        = 0;
			if ( $transactions ) {
				$total = 0;
				/** @var TransactionEntity $transaction */
				foreach ( $transactions as $transaction ) {
					if ( 'paid' === $transaction->status ) {
						$refunds = $transaction->get_refund();
						if ( $refunds ) {
							$total = $total + $refunds->remaining;
						} else {
							$total = $total + $transaction->value;
						}
					}
				}
				$campaigns[ $key ]['last_donation'] = end( $transactions )->created;
				$campaigns[ $key ]['transactions']  = count( $transactions );
				$campaigns[ $key ]['currency']      = $transactions[0]->currency;
				$campaigns[ $key ]['total']         = $total;
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
	public function column_names(): array {
		return [
			'label'         => __( 'Label', 'kudos-donations' ),
			'transactions'  => __( 'Transactions', 'kudos-donations' ),
			'total'         => __( 'Total', 'kudos-donations' ),
			'last_donation' => __( 'Last Donation', 'kudos-donations' ),
		];
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 * @since   2.0.4
	 */
	public function get_hidden_columns(): array {
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
	public function get_sortable_columns(): array {
		return [
			'date'          => [
				'date',
				false,
			],
			'total'         => [
				'total',
				false,
			],
			'last_donation' => [
				'last_donation',
				false,
			],
		];
	}

	/**
	 * Process cancel and bulk-cancel actions
	 *
	 * @since   2.0.4
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered.
		switch ( $this->current_action() ) {

			case 'delete':
				// In our file that handles the request, verify the nonce.
				if ( isset( $_REQUEST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ),
						'bulk-' . $this->_args['singular'] ) ) {
					die();
				}

				if ( isset( $_GET['label'] ) ) {
					self::delete_record( sanitize_text_field( wp_unslash( $_GET['label'] ) ) );
				}

				break;

			case 'bulk-delete':
				// In our file that handles the request, verify the nonce.
				if ( isset( $_REQUEST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ),
						'bulk-' . $this->_args['plural'] ) ) {
					die();
				}

				if ( isset( $_REQUEST['bulk-action'] ) ) {
					$labels = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['bulk-action'] ) );
					foreach ( $labels as $label ) {
						self::delete_record( sanitize_text_field( $label ) );
					}
				}
				break;
		}
	}

	/**
	 * Delete a campaign.
	 *
	 * @param string $label The campaign label.
	 *
	 * @return bool
	 * @since   2.0.4
	 */
	protected function delete_record( string $label ): bool {

		$labels = Settings::get_setting( 'campaign_labels' );
		$labels = array_filter(
			$labels,
			function ( $a ) use ( $label ) {
				return ! in_array( $label, $a, true );
			}
		);

		return Settings::update_setting( 'campaign_labels', $labels );

	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since   2.0.4
	 */
	protected function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />',
			$item['name']
		);
	}

	/**
	 * Time (date) column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since   2.0.4
	 */
	protected function column_date( array $item ): string {

		return __( 'Added', 'kudos-donations' ) . '<br/>' .
		       wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			       strtotime( $item['date'] ) );
	}

	/**
	 * Label column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since 2.0.4
	 */
	protected function column_label( array $item ): string {

		return $item['name'];

	}

	/**
	 * Transactions column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since 2.0.4
	 */
	protected function column_transactions( array $item ): string {

		return sprintf(
			'<a href=%1$s>%2$s</a>',
			sprintf( admin_url( 'admin.php?page=kudos-transactions&search-field=campaign_label&s=%s' ), rawurlencode( $item['slug'] ) ),
			strtoupper( $item['transactions'] )
		);

	}

	/**
	 * Total column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since 2.0.4
	 */
	protected function column_total( array $item ): string {

		$currency = ! empty( $item['currency'] ) ? Utils::get_currency_symbol( $item['currency'] ) : '';
		$total    = $item['total'];

		return $currency . ' ' . number_format_i18n( $total, 2 );

	}

	/**
	 * Shows the date of the last translation
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since 2.0.5
	 */
	protected function column_last_donation( array $item ): string {

		return isset( $item['last_donation'] ) ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			strtotime( $item['last_donation'] ) ) : '';

	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array|string
	 * @since   2.0.4
	 */
	protected function get_bulk_actions() {
		return [
			'bulk-delete' => __( 'Delete', 'kudos-donations' ),
		];
	}
}
