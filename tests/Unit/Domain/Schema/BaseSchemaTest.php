<?php
/**
 * BaseSchema tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Schema;

use IseardMedia\Kudos\Domain\Schema\CampaignSchema;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Domain\Schema\BaseSchema
 */
class BaseSchemaTest extends BaseTestCase {

	private CampaignSchema $schema;

	public function set_up(): void {
		parent::set_up();
		$this->schema = $this->get_from_container( CampaignSchema::class );
	}

	public function test_cast_types_integer(): void {
		$result = $this->schema->cast_types( [ 'id' => '5' ] );
		$this->assertSame( 5, $result['id'] );
	}

	public function test_cast_types_integer_non_numeric_returns_null(): void {
		$result = $this->schema->cast_types( [ 'id' => 'abc' ] );
		$this->assertNull( $result['id'] );
	}

	public function test_cast_types_float(): void {
		$result = $this->schema->cast_types( [ 'goal' => '10.50' ] );
		$this->assertSame( 10.5, $result['goal'] );
	}

	public function test_cast_types_float_empty_string_returns_null(): void {
		$result = $this->schema->cast_types( [ 'goal' => '' ] );
		$this->assertNull( $result['goal'] );
	}

	public function test_cast_types_boolean_true(): void {
		$result = $this->schema->cast_types( [ 'show_goal' => '1' ] );
		$this->assertTrue( $result['show_goal'] );
	}

	public function test_cast_types_boolean_false(): void {
		$result = $this->schema->cast_types( [ 'show_goal' => '0' ] );
		$this->assertFalse( $result['show_goal'] );
	}

	public function test_cast_types_object_from_json_string(): void {
		$json   = '["5","10","25"]';
		$result = $this->schema->cast_types( [ 'fixed_amounts' => $json ] );
		$this->assertSame( [ '5', '10', '25' ], $result['fixed_amounts'] );
	}

	public function test_cast_types_object_invalid_json_returns_null(): void {
		$result = $this->schema->cast_types( [ 'fixed_amounts' => 'not json{' ] );
		$this->assertNull( $result['fixed_amounts'] );
	}

	public function test_cast_types_object_array_passes_through(): void {
		$array  = [ '5', '10' ];
		$result = $this->schema->cast_types( [ 'fixed_amounts' => $array ] );
		$this->assertSame( $array, $result['fixed_amounts'] );
	}

	public function test_cast_types_ignores_keys_not_in_schema(): void {
		$result = $this->schema->cast_types( [ 'nonexistent_field' => 'value' ] );
		$this->assertSame( 'value', $result['nonexistent_field'] );
	}

	public function test_sanitize_data_from_schema_filters_unknown_keys(): void {
		$data   = [
			'title'            => 'Test',
			'unknown_field'    => 'should be removed',
		];
		$result = $this->schema->sanitize_data_from_schema( $data );

		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayNotHasKey( 'unknown_field', $result );
	}

	public function test_sanitize_data_from_schema_empty_string_becomes_null(): void {
		$result = $this->schema->sanitize_data_from_schema( [ 'title' => '' ] );
		$this->assertNull( $result['title'] );
	}

	public function test_get_column_schema_returns_consistent_result(): void {
		$first  = $this->schema->get_column_schema();
		$second = $this->schema->get_column_schema();
		$this->assertSame( $first, $second );
	}
}
