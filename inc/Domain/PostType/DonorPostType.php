<?php
/**
 * Donor Post Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasAdminColumns;
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;

class DonorPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasAdminColumns {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_donor';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Donor';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Donor', 'Donor post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Donors', 'Donor post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			'email'              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_email',
			],
			'mode'               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'name'               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'business_name'      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'street'             => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'postcode'           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'city'               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'country'            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'vendor_customer_id' => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns_config(): array {
		return [
			'name'               => [
				'label'      => __( 'Name', 'kudos-donations' ),
				'value_type' => FieldType::STRING,
			],
			'email'              => [
				'label'      => __( 'Email', 'kudos-donations' ),
				'value_type' => FieldType::EMAIL,
			],
			'vendor_customer_id' => [
				'label'      => __( 'Vendor ID', 'kudos-donations' ),
				'value_type' => FieldType::STRING,
			],
			'total_donations'    => [
				'label'          => __( 'Total donated', 'kudos-donations' ),
				'value_type'     => FieldType::INTEGER,
				'value_callback' => function( $donor_id ) {
					$request = new \WP_REST_Request( 'GET', '/kudos/v1/transaction/donor/total' );
					$request->set_query_params( [ 'donor_id' => $donor_id ] );
					$response = rest_do_request( $request );
					return $response->data;
				},
			],
		];
	}
}
