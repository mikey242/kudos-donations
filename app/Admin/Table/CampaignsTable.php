<?php

namespace Kudos\Admin\Table;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Campaigns;
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
	 * @var Campaigns
	 */
	private $campaigns;

	/**
	 * Class constructor
	 *
	 * @since   2.0.4
	 */
	public function __construct() {

		$this->mapper    = new MapperService( TransactionEntity::class );
		$this->table     = $this->mapper->get_table_name();
		$this->campaigns = new Campaigns();

		$this->search_columns = [
			'name' => __( 'Name', 'kudos-donations' ),
		];

		$this->export_columns = [
			'name'         => __( 'Name', 'kudos-donations' ),
			'transactions' => __( 'Transactions', 'kudos-donations' ),
			'goal'         => __( 'Goal', 'kudos-donations' ),
			'total'        => __( 'Total', 'kudos-donations' ),
		];

		parent::__construct(
			[
				'orderBy'  => 'date',
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

		$search = $this->get_search_data();

		$campaigns = $this->campaigns->get_all();
		if ( ! $campaigns ) {
			return [];
		}

		// Add search query if exist.
		if ( $search ) {
			$campaigns = array_filter( $campaigns,
				function ( $value ) use ( $search ) {
					return ( strtolower( $value[ $search['field'] ] ) == strtolower( $search['term'] ) );
				}
			);
		}

		foreach ( $campaigns as $key => $campaign ) {
			$id = $campaign['id'];

			$campaign_total = $this->campaigns::get_campaign_stats( $id );

			$campaigns[ $key ]['date']          = date( "r", hexdec( substr( $id, 3, 8 ) ) );
			$campaigns[ $key ]['transactions']  = 0;
			$campaigns[ $key ]['goal']          = $campaign['campaign_goal'];
			$campaigns[ $key ]['total']         = 0;
			$campaigns[ $key ]['currency']      = 'EUR';
			$campaigns[ $key ]['total']         = $campaign_total['total'];
			$campaigns[ $key ]['last_donation'] = $campaign_total['last_donation'];
			$campaigns[ $key ]['transactions']  = $campaign_total['count'];
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
			'date'          => __( 'Date', 'kudos-donations' ),
			'name'          => __( 'Name', 'kudos-donations' ),
			'transactions'  => __( 'Transactions', 'kudos-donations' ),
			'total'         => __( 'Total', 'kudos-donations' ),
			'goal'          => __( 'Goal', 'kudos-donations' ),
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
			'date',
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
	protected function column_name( array $item ): string {

		$url = add_query_arg( [
			'page'        => 'kudos-settings',
			'tab_name'    => 'campaigns',
			'campaign_id' => $item['id'],
		],
			admin_url() );

		$actions = [
			'edit' => sprintf(
				'<a href="%s">%s</a>',
				$url,
				__( 'Edit', 'kudos-donations' )
			),
		];

		return $item['name'] . $this->row_actions( $actions );

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
			sprintf( admin_url( 'admin.php?page=kudos-transactions&search-field=campaign_id&s=%s' ),
				rawurlencode( $item['id'] ) ),
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
	 * Goal column
	 *
	 * @param array $item Array of results.
	 *
	 * @return string
	 * @since 2.3.2
	 */
	protected function column_goal( array $item ): string {

		$currency = ! empty( $item['currency'] ) ? Utils::get_currency_symbol( $item['currency'] ) : '';
		$total    = $item['goal'];

		return $currency . ' ' . ( is_numeric( $total ) ? number_format_i18n( $total, 2 ) : '' );

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
			strtotime( $item['last_donation'] ) ) : sprintf( "<i>%s</i>", __( 'None yet', 'kudos-donations' ) );

	}
}
