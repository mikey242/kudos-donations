<?php
/**
 * RegistrableHandler tests.
 */

namespace IseardMedia\Kudos\Tests\Container\Handler;

use IseardMedia\Kudos\Container\Delayed;
use IseardMedia\Kudos\Container\Handler\RegistrableHandler;
use IseardMedia\Kudos\Container\Registrable;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\Argument\ServiceLocator;
use Psr\Log\NullLogger;
use RuntimeException;
use WP_UnitTestCase;

/**
 * @covers \IseardMedia\Kudos\Container\Handler\RegistrableHandler
 */
class RegistrableHandlerTest extends WP_UnitTestCase {

	/**
	 * Services built by the locator, in order.
	 *
	 * @var string[]
	 */
	public static array $built = [];

	/**
	 * Services whose register() ran, in order.
	 *
	 * @var string[]
	 */
	public static array $registered = [];

	public function set_up(): void {
		parent::set_up();
		self::$built      = [];
		self::$registered = [];
	}

	/**
	 * Builds a handler over a real container ServiceLocator for the given service classes.
	 *
	 * @param array<string, callable> $factories Service id => factory.
	 */
	private function make_handler( array $factories ): RegistrableHandler {
		$locator = new ServiceLocator(
			static function ( string $id ) use ( $factories ) {
				self::$built[] = $id;
				return $factories[ $id ]();
			},
			array_map( static fn( $id ) => [ $id ], array_combine( array_keys( $factories ), array_keys( $factories ) ) ),
			array_combine( array_keys( $factories ), array_keys( $factories ) )
		);

		$handler = new RegistrableHandler( $locator );
		$handler->setLogger( new NullLogger() );
		return $handler;
	}

	/**
	 * A Delayed service is not built until its registration action fires.
	 */
	public function test_delayed_service_is_built_only_when_its_action_fires(): void {
		$handler = $this->make_handler( [ DelayedStub::class => static fn() => new DelayedStub() ] );

		$handler->process();
		$this->assertSame( [], self::$built );

		do_action( DelayedStub::get_registration_action() );
		$this->assertSame( [ DelayedStub::class ], self::$built );
		$this->assertSame( [ DelayedStub::class ], self::$registered );
	}

	/**
	 * A non-Delayed service is built and registered immediately.
	 */
	public function test_immediate_service_is_registered_on_process(): void {
		$handler = $this->make_handler( [ ImmediateStub::class => static fn() => new ImmediateStub() ] );

		$handler->process();
		$this->assertSame( [ ImmediateStub::class ], self::$registered );
	}

	/**
	 * A disabled service is built (to ask is_enabled) but not registered.
	 */
	public function test_disabled_service_is_not_registered(): void {
		$handler = $this->make_handler( [ DisabledStub::class => static fn() => new DisabledStub() ] );

		$handler->process();
		do_action( DisabledStub::get_registration_action() );
		$this->assertSame( [ DisabledStub::class ], self::$built );
		$this->assertSame( [], self::$registered );
	}

	/**
	 * A service that fails to build is skipped without breaking the others on the same action.
	 */
	public function test_build_failure_is_contained(): void {
		$handler = $this->make_handler(
			[
				FailingStub::class => static function () {
					throw new RuntimeException( 'boom' );
				},
				DelayedStub::class => static fn() => new DelayedStub(),
			]
		);

		$handler->process();
		do_action( DelayedStub::get_registration_action() );
		$this->assertSame( [ DelayedStub::class ], self::$registered );
	}
}

// phpcs:disable Generic.Files.OneObjectStructurePerFile

class ImmediateStub implements Registrable {
	public function register(): void {
		RegistrableHandlerTest::$registered[] = static::class;
	}
	public function is_enabled(): bool {
		return true;
	}
}

class DelayedStub extends ImmediateStub implements Delayed {
	public static function get_registration_action(): string {
		return 'kudos_test_registrable_action';
	}
	public static function get_registration_action_priority(): int {
		return 10;
	}
}

class DisabledStub extends DelayedStub {
	public function is_enabled(): bool {
		return false;
	}
}

class FailingStub extends DelayedStub {}
