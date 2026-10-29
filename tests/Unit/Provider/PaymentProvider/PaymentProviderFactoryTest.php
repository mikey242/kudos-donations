<?php
namespace IseardMedia\Kudos\Tests\Provider\PaymentProvider;

use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory
 */
class PaymentProviderFactoryTest extends BaseTestCase {

	/**
	 * @dataProvider active_provider_provider
	 *
	 * @param string $saved    The saved vendor slug.
	 * @param string $expected The slug of the provider that should be active.
	 */
	public function test_get_active_provider_resolves_saved_vendor_or_default( string $saved, string $expected ): void {
		update_option( PaymentService::SETTING_VENDOR, $saved );

		/** @var PaymentProviderFactory $provider_factory */
		$provider_factory = $this->get_from_container( PaymentProviderFactory::class );
		$active           = $provider_factory->get_active_provider();

		$this->assertNotNull( $active );
		$this->assertSame( $expected, $active::get_slug() );
	}

	public function active_provider_provider(): array {
		return [
			'saved vendor'                => [ 'stripe', 'stripe' ],
			'nothing saved'               => [ '', 'mollie' ],
			'saved vendor is not enabled' => [ 'no-longer-installed', 'mollie' ],
		];
	}

	/**
	 * With Stripe active, the other enabled vendors must still have their webhook status-change
	 * handlers registered, so subscriptions on a previously active provider keep being processed
	 * after a switch.
	 */
	public function test_registers_status_change_handlers_for_all_enabled_vendors(): void {
		update_option( PaymentService::SETTING_VENDOR, 'stripe' );

		$provider_factory = $this->get_from_container( PaymentProviderFactory::class );
		$provider_factory->register();

		$this->assertTrue( has_action( 'kudos_mollie_handle_status_change' ), 'Mollie handler should be wired even when not active.' );
		$this->assertTrue( has_action( 'kudos_stripe_handle_status_change' ), 'Active provider handler should be wired.' );
	}
}