<?php
namespace IseardMedia\Kudos\Tests\Provider\PaymentProvider;

use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory::register
 */
class PaymentProviderFactoryTest extends BaseTestCase {

	/**
	 * With demo active, the other enabled vendors must still have their webhook status-change
	 * handlers registered, so subscriptions on a previously active provider keep being processed
	 * after a switch.
	 */
	public function test_registers_status_change_handlers_for_all_enabled_vendors(): void {
		update_option( PaymentService::SETTING_VENDOR, 'demo' );

		$provider_factory = $this->get_from_container( PaymentProviderFactory::class );
		$provider_factory->register();

		$this->assertTrue( has_action( 'kudos_mollie_handle_status_change' ), 'Mollie handler should be wired even when not active.' );
		$this->assertTrue( has_action( 'kudos_stripe_handle_status_change' ), 'Stripe handler should be wired even when not active.' );
		$this->assertTrue( has_action( 'kudos_demo_handle_status_change' ), 'Active provider handler should be wired.' );
	}
}