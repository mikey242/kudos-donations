<?php
/**
 * TableColumnsTrait
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

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
			$this->remove_actions( $post_type );
			$this->config_column_headers( $post_type, $column_config );
			$this->populate_columns( $post_type, $column_config );
			$this->make_columns_sortable( $post_type, $column_config );
			$this->add_column_sorting( $post_type, $column_config );
		}
	}

	/**
	 * Removes several actions from table.
	 *
	 * @param string $post_type The post type.
	 */
	private function remove_actions( string $post_type ): void {
		// Remove bulk actions.
		add_filter(
			'bulk_actions-edit-' . $post_type,
			function ( $actions ) {
				if ( ! KUDOS_DEBUG ) {
					unset( $actions['edit'] );
				}
				return $actions;
			}
		);

		// Remove row actions.
		add_filter(
			'post_row_actions',
			function ( $actions, $post ) use ( $post_type ) {
				if ( $post_type === $post->post_type ) {
					if ( ! KUDOS_DEBUG ) {
						unset( $actions['edit'] );
					}
					unset( $actions['inline hide-if-no-js'] ); // Quick edit.
				}
				return $actions;
			},
			10,
			2
		);
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
				return array_merge(
					[ 'cb' => '<input type="checkbox" />' ], // Move checkbox to beginning.
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
					if ( isset( $additional_columns[ $column_name ]['value'] ) ) {
						$meta_field_value = \is_callable( $additional_columns[ $column_name ]['value'] ) ? \call_user_func( $additional_columns[ $column_name ]['value'], $post_id ) : $additional_columns[ $column_name ]['value'];
					} else {
						$meta_field_value = get_post_meta( $post_id, $column_name, true );
					}
					$meta_value_type   = $additional_columns[ $column_name ]['value_type'] ?? FieldType::STRING;
					$meta_field_output = $meta_field_value
						? $this->prepare_values( $meta_value_type, $meta_field_value )
						: '—';
					echo wp_kses(
						$meta_field_output,
						[
							'a'    => [
								'href'   => [],
								'class'  => [],
								'title'  => [],
								'target' => [],
							],
							'img'  => [
								'src'   => [],
								'title' => [],
							],
							'div'  => [
								'class' => [],
								'style' => [],
								'title' => [],
							],
							'svg'  => [
								'xmlns'        => [],
								'viewbox'      => [],
								'stroke'       => [],
								'stroke-width' => [],
								'fill'         => [],
							],
							'path' => [
								'd' => [],
							],
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
	private function prepare_values( string $type, $value ): string {

		switch ( $type ) {
			case FieldType::STRING:
				$result = $value;
				break;
			case FieldType::INTEGER:
				$result = esc_html( $value );
				break;
			case FieldType::URL:
				$result = esc_url( $value );
				break;
			case FieldType::IMAGE:
				$result = '<img src="' . esc_url( $value ) . '"/>';
				break;
			case FieldType::EMAIL:
				$result = '<a href="mailto:' . $value . '">' . $value . '</a>';
				break;
			case FieldType::ARRAY:
				$result = esc_html( implode( ', ', $value ) );
				break;
			default:
				$result = $value;
		}

		return $result;
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
					$value_type = $property['value_type'];

					switch ( $value_type ) {
						case FieldType::INTEGER:
							$sort_method = 'meta_value_num';
							break;
						default:
							$sort_method = 'meta_value';
					}

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
			if ( $use_label ) {
				$post_columns[ $name ] = ! empty( $config['label'] ) ? ( \is_callable( $config['label'] ) ? \call_user_func( $config['label'] ) : $config['label'] ) : $name;
			} else {
				$post_columns[ $name ] = $name;
			}
		}
		return $post_columns;
	}
}
