<?php
/**
 * Front tests
 */

namespace IseardMedia\Kudos\Tests\Controller;

use IseardMedia\Kudos\Controller\Front;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Domain\Schema\TransactionSchema;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use WP_UnitTestCase;

/**
 * @covers \IseardMedia\Kudos\Controller\Front
 */
class FrontTest extends WP_UnitTestCase {

	/**
	 * Test that css file(s) are registered.
	 */
	public function test_css_registered() {
		global $wp_styles;
		$this->assertContains( Front::STYLE_HANDLE_VIEW, array_keys( $wp_styles->registered ), 'iseardmedia-kudos-button-style stylesheet not registered' );
	}

	/**
	 * Test that css file(s) are registered.
	 */
	public function test_js_registered() {
		global $wp_scripts;
        foreach( Front::SCRIPT_HANDLES as $handle) {
            $this->assertContains( $handle, array_keys( $wp_scripts->registered ), 'iseardmedia-kudos-button-script script not registered' );
        }
	}

	/**
	 * Test that plugin container is created.
	 */
	public function test_render_callback() {
		$mollie_mock = $this->createMock(MolliePaymentProvider::class);
		$mollie_mock->method( 'is_vendor_ready' )->willReturn(true);
		$vendor_factory = $this->createMock( PaymentProviderFactory::class );
		$vendor_factory->method('get_provider')->willReturn($mollie_mock);

		$front          = new Front( $vendor_factory, new TransactionRepository(new WpDb(), new TransactionSchema()) );
		$args           = [
			'campaign_id'  => 291,
			'button_label' => 'Donate now',
			'type'         => 'button',
		];
		$html           = $front->kudos_render_callback( $args );
		$this->assertSame( true, \is_string( $html ) );
	}
}
