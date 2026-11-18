<?php
/**
 * Localization tests.
 */

namespace IseardMedia\Kudos\Tests\Helper;

use IseardMedia\Kudos\Helper\Localization;
use ReflectionClass;
use WP_UnitTestCase;

/**
 * @covers \IseardMedia\Kudos\Helper\Localization
 */
class LocalizationTest extends WP_UnitTestCase {

	/**
	 * Static state saved before each test, as other tests share it.
	 */
	private array $saved = [];

	public function set_up(): void {
		parent::set_up();
		foreach ( ( new ReflectionClass( Localization::class ) )->getProperties() as $property ) {
			$property->setAccessible( true );
			$this->saved[ $property->getName() ] = $property->getValue();
		}
	}

	public function tear_down(): void {
		foreach ( $this->saved as $name => $value ) {
			$property = new \ReflectionProperty( Localization::class, $name );
			$property->setAccessible( true );
			$property->setValue( null, $value );
		}
		parent::tear_down();
	}

	/**
	 * A lazy global is not computed until the data is read, and only once.
	 */
	public function test_lazy_global_resolves_once_on_output(): void {
		$calls = 0;
		Localization::add_global_lazy(
			'lazyValue',
			static function () use ( &$calls ) {
				++$calls;
				return 'computed';
			}
		);
		$this->assertSame( 0, $calls );

		$this->assertSame( 'computed', Localization::get_front()['lazyValue'] );
		$this->assertSame( 'computed', Localization::get_admin()['lazyValue'] );
		$this->assertSame( 'computed', Localization::get_global()['lazyValue'] );
		$this->assertSame( 1, $calls );
	}
}
