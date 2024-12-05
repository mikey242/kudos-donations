<?php
/**
 * Plugin tests
 */

namespace Controller;

use IseardMedia\Kudos\Vendor\PaymentVendor\PaymentVendorInterface;
use WP_UnitTestCase;

/**
 * Sample test case.
 */
class Front extends WP_UnitTestCase {

	/**
	 * Test that css file(s) are registered.
	 */
	public function test_css_registered() {
		global $wp_styles;
		$this->assertContains( \IseardMedia\Kudos\Controller\Front::STYLE_HANDLE_VIEW, array_keys( $wp_styles->registered ), 'iseardmedia-kudos-button-style stylesheet not registered' );
	}

	/**
	 * Test that css file(s) are registered.
	 */
	public function test_js_registered() {
		global $wp_scripts;
        foreach(\IseardMedia\Kudos\Controller\Front::SCRIPT_HANDLES as $handle) {
            $this->assertContains( $handle, array_keys( $wp_scripts->registered ), 'iseardmedia-kudos-button-script script not registered' );
        }
	}

	/**
	 * Test that plugin container is created.
	 */
	public function test_render_callback() {
		$vendor_service = $this->createMock( PaymentVendorInterface::class );
		$front          = new \IseardMedia\Kudos\Controller\Front( $vendor_service );
		$args           = [
			'campaign_id'  => 291,
			'button_label' => 'Donate now',
			'type'         => 'button',
		];
		$html           = $front->kudos_render_callback( $args );
		$this->assertSame( true, \is_string( $html ) );
	}
}
