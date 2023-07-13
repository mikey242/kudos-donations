<?php
/**
 * TableColumnsTrait
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure\Admin;

use IseardMedia\Kudos\Enum\FieldType;

trait TableColumnsTrait {

	/**
	 * Register additional table columns.
	 *
	 * @param string $post_type The post type to add columns for.
	 * @param array  $column_config Array of column config.
	 */
	protected function add_table_columns( string $post_type, array $column_config ): void {

		if ( ! empty( $column_config ) ) {
			$this->config_column_headers( $post_type, $column_config );
			$this->populate_columns( $post_type, $column_config );
			$this->make_columns_sortable( $post_type, $column_config );
			$this->add_column_sorting( $post_type, $column_config );
		}
	}

	/**
	 * Add the custom columns to the specific table .
	 *
	 * @param string $post_type The post_type where the columns should be added.
	 * @param array  $column_config The custom-field columns that are required in the table.
	 */
	private function config_column_headers( string $post_type, array $column_config ): void {
		// Generate column list as an associative array of “column name” ⇒ “label”.
		$post_columns = $this->get_column_list( $column_config );
		add_filter(
			'manage_' . $post_type . '_posts_columns',
			static function ( $columns ) use ( $post_columns ) {
				add_filter( 'post_date_column_status', static fn() => null, 10, 2 );
				unset( $columns['title'] );
				unset( $columns['cb'] );
				return array_merge(
					[ 'cb' => '<input type="checkbox" />' ],
					array_map( static fn( $column ) => $column ?? '', $post_columns ),
					$columns
				);
			}
		);
	}

	/**
	 * Populate the custom columns of the table with their meta-field values.
	 *
	 * @param string $post_type The linked post_type.
	 * @param array  $additional_columns The custom-field columns that are required in the table.
	 */
	private function populate_columns( string $post_type, array $additional_columns ): void {
		add_action(
			'manage_' . $post_type . '_posts_custom_column',
			function ( $column_name, $post_id ) use ( $additional_columns ): void {
				if ( ! empty( $additional_columns[ $column_name ] ) ) {
					if ( isset( $additional_columns[ $column_name ]['value_callback'] ) ) {
						$meta_field_value = \call_user_func( $additional_columns[ $column_name ]['value_callback'], $post_id );
					} else {
						$meta_field_value = get_post_meta( $post_id, $column_name, single: true );
					}
					$meta_value_type   = $additional_columns[ $column_name ]['value_type'];
					$meta_field_output = $meta_field_value
						? $this->prepare_values( $meta_value_type, $meta_field_value )
						: '—';
					echo wp_kses(
						$meta_field_output,
						[
							'a'    => [ 'href' => [] ],
							'img'  => [ 'src' => [] ],
							'span' => [ 'class' => [] ],
						]
					);
				}
			},
			10,
			2
		);
	}

	/**
	 * Get the value output for the post.
	 *
	 * @param string $type Value type of meta-field.
	 * @param mixed  $value Value of meta-field.
	 */
	private function prepare_values( string $type, mixed $value ): string {
		return match ($type) {
			FieldType::STRING => $value,
			FieldType::INTEGER  => esc_html( $value ),
			FieldType::URL => esc_url( $value ),
			FieldType::IMAGE => '<img src="' . esc_url( $value ) . '"/>',
			FieldType::EMAIL => '<a href="mailto:' . $value . '">' . $value . '</a>',
			'array' => esc_html( implode( ', ', $value ) )
		};
	}

	/**
	 * Allow user to sort data by custom columns.
	 *
	 * @param string $post_type The linked post_type.
	 * @param array  $column_config The custom-field table-columns that need to be sortable.
	 */
	private function make_columns_sortable( string $post_type, array $column_config ): void {
		$post_columns = $this->get_column_list( $column_config, false );
		add_filter(
			'manage_edit-' . $post_type . '_sortable_columns',
			static fn( $columns ) => array_merge( $columns, $post_columns )
		);
	}

	/**
	 * Updates the query to sort posts by meta field.
	 *
	 * @param string $post_type The object subtype.
	 * @param array  $column_config Array of additional columns.
	 */
	private function add_column_sorting( string $post_type, array $column_config ): void {
		add_action(
			'pre_get_posts',
			function ( $query ) use ( $post_type, $column_config ): void {
				if ( ! is_admin() || ! $query->is_main_query() || $post_type !== $query->get( 'post_type' ) ) {
					return;
				}

				$field = $query->get( 'orderby' );

				$property = $column_config[ $field ] ?? null;

				if ( ! empty( $property ) ) {

					// Get value type to chose correct sorting method.
					$value_type  = $property['value_type'];
					$sort_method = match ($value_type) {
						FieldType::INTEGER => 'meta_value_num',
						default => 'meta_value'
					};

					// Allow for empty and non-empty results.
					$query->set(
						'meta_query',
						[
							'relation' => 'OR',
							[
								'key'     => $field,
								'compare' => 'NOT EXISTS',
							],
							[
								'key'     => $field,
								'compare' => 'EXISTS',
							],
						]
					);

					// Update query with correct sort method.
					$query->set( 'orderby', $sort_method );
				}
			}
		);
	}

	/**
	 * Returns an array of key => value pairs.
	 *
	 * @param array $column_config The post type meta config.
	 * @param bool  $use_label Whether to use the label key for the value.
	 */
	private function get_column_list( array $column_config, bool $use_label = true ): array {
		$post_columns = [];
		foreach ( $column_config as $name => $config ) {
			$post_columns[ $name ] = $use_label ? $config['label'] : $name;
		}
		return $post_columns;
	}
}
