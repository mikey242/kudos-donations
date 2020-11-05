<?php

namespace Kudos\Admin\Table;

/*
 * https://gist.github.com/paulund/7659452
 * https://github.com/pmbaldha/WP-Custom-List-Table-With-Database-Example/blob/master/custom-list-table-db-example.php
 * https://github.com/collizo4sky/WP_List_Table-Class-Plugin-Example/blob/master/plugin.php
 */

trait TableTrait {

	/**
	 * The table name.
	 *
	 * @var false|string
	 */
	public $table;


	/**
	 * Array of column names and their names on export.
	 *
	 * @var array
	 */
	public $export_columns;

	/**
	 * Array of column values and names to use in search.
	 *
	 * @var array
	 */
	private $search_columns;

	/**
	 * Message to show when no transactions available
	 *
	 * @since      1.0.0
	 */
	public function no_items() {

		/* translators: %s: Name of record type (e.g transactions) */
		printf( esc_html__( 'No %s found.', 'kudos-donations' ), esc_attr( $this->_args['plural'] ) );

	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @param string $which helps you decide if you add the markup after (bottom) or before (top) the list.
	 *
	 * @since   1.0.0
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' === $which ) {
			if ( $this->has_items() ) {
				echo esc_attr( apply_filters( 'kudos_table_tablenav_top', '', $this->_args ) );
			}
		}

	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param array  $item Data.
	 * @param string $column_name Current column name.
	 *
	 * @return mixed
	 * @since      1.0.0
	 */
	public function column_default( $item, $column_name ) {

		return $item[ $column_name ];

	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 *
	 * @return void
	 * @since      1.0.0
	 */
	public function prepare_items() {

		// Process bulk action if any.
		static::process_bulk_action();

		$columns               = $this->get_columns();
		$hidden                = static::get_hidden_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$table_data = static::fetch_table_data();
		usort( $table_data, [ &$this, 'sort_data' ] );

		$items_per_page = 20;
		$current_page   = $this->get_pagenum();
		$this->items    = array_slice( $table_data, ( ( $current_page - 1 ) * $items_per_page ), $items_per_page );
		$total_items    = count( $table_data );
		$this->set_pagination_args(
			[
				'total_items' => count( $this->items ),
				'per_page'    => $items_per_page,
				'total_pages' => ceil( $total_items / $items_per_page ),
			]
		);

	}

	/**
	 * Columns to show
	 *
	 * @return array
	 * @since      2.0.0
	 */
	public function get_columns() {

		$columns['cb'] = '<input type="checkbox" />';

		return array_merge( $columns, static::column_names() );

	}

	public function get_search_data() {

		$search = null;

		if ( isset( $_REQUEST['s'] ) && isset( $_REQUEST['search-field'] ) && array_key_exists( $_REQUEST['search-field'], $this->column_names() ) ) {
			$search['term'] = strtolower( esc_attr( wp_unslash( $_REQUEST['s'] ) ) );
			$search['field'] = esc_attr( wp_unslash( $_REQUEST['search-field'] ) );
		}

		return $search;

	}

	/**
	 * Get the table data
	 *
	 * @return array
	 * @since   1.0.0
	 */
	abstract public function fetch_table_data();

	/**
	 * Displays the search box.
	 *
	 * @since 3.1.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}
		$input_id = $input_id . '-search-input';
		$search_field = $this->get_search_data()['field'];

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<label for="search-field-selector" class="screen-reader-text"><?php _e('Select search field', 'kudos-donations') ?>></label>
			<select name="search-field" style="vertical-align: baseline" id="search-field-selector">
				<option value="-1"><?php _e('Search field', 'kudos-donations') ?></option>
				<?php
				foreach ($this->search_columns as $value=>$label) {
					echo "<option ". ($value === $search_field ? "selected" : '') ." value=$value>$label</option>";
				}
				?>
			</select>
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @param array $a First array.
	 * @param array $b Second array.
	 *
	 * @return Mixed
	 * @since      1.0.0
	 */
	private function sort_data( array $a, array $b ) {

		// Set defaults.
		$order_by = $this->_args['orderBy'] ?? 'time';
		$order    = $this->_args['order'] ?? 'desc';

		// If orderBy is set, use this as the sort column.
		if ( ! empty( $_GET['orderby'] ) ) {
			$order_by = $_GET['orderby'];
		}

		// If order is set use this as the order.
		if ( ! empty( $_GET['order'] ) ) {
			$order = sanitize_text_field( $_GET['order'] );
		}

		$result = strcmp( $a[ $order_by ], $b[ $order_by ] );

		if ( 'asc' === $order ) {
			return $result;
		}

		return - $result;

	}
}
