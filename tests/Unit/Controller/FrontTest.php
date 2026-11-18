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
		$vendor_factory->method( 'get_active_provider' )->willReturn($mollie_mock);

		$front          = new Front( $vendor_factory, new TransactionRepository(new WpDb(), new TransactionSchema()) );
		$args           = [
			'campaign_id'  => 291,
			'button_label' => 'Donate now',
			'type'         => 'button',
		];
		$html           = $front->kudos_render_callback( $args );
		$this->assertSame( true, \is_string( $html ) );
	}

	/**
	 * A Front instance whose vendor is ready, so the form renders.
	 */
	private function create_front(): Front {
		$mollie_mock = $this->createMock( MolliePaymentProvider::class );
		$mollie_mock->method( 'is_vendor_ready' )->willReturn( true );
		$vendor_factory = $this->createMock( PaymentProviderFactory::class );
		$vendor_factory->method( 'get_active_provider' )->willReturn( $mollie_mock );

		return new Front( $vendor_factory, new TransactionRepository( new WpDb(), new TransactionSchema() ) );
	}

	public function test_render_records_placement_url_on_public_page(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'publish' ] );
		$this->go_to( get_permalink( $page_id ) . '?utm_source=test' );

		$this->create_front()->kudos_render_callback( [ 'campaign_id' => 1 ] );

		$this->assertTrue( Front::is_form_placed() );
		$this->assertSame( get_permalink( $page_id ), get_option( Front::SETTING_FORM_PLACED_URL ) );
	}

	public function test_render_keeps_first_placement_url(): void {
		update_option( Front::SETTING_FORM_PLACED_URL, 'https://example.org/first/' );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'publish' ] );
		$this->go_to( get_permalink( $page_id ) );

		$this->create_front()->kudos_render_callback( [ 'campaign_id' => 1 ] );

		$this->assertSame( 'https://example.org/first/', get_option( Front::SETTING_FORM_PLACED_URL ) );
	}

	public function test_render_does_not_record_in_preview(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'draft' ] );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->go_to( add_query_arg( [ 'page_id' => $page_id, 'preview' => 'true' ], home_url( '/' ) ) );

		$this->create_front()->kudos_render_callback( [ 'campaign_id' => 1 ] );

		$this->assertFalse( Front::is_form_placed() );
	}

	public function test_render_does_not_record_in_admin(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		set_current_screen( 'dashboard' );

		$this->create_front()->kudos_render_callback( [ 'campaign_id' => 1 ] );

		set_current_screen( 'front' );
		$this->assertFalse( Front::is_form_placed() );
	}

	public function test_render_does_not_record_without_campaign(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		$this->go_to( home_url( '/' ) );

		$this->create_front()->kudos_render_callback( [ 'campaign_id' => '' ] );

		$this->assertFalse( Front::is_form_placed() );
	}

	/**
	 * window.kudos is only attached to Kudos scripts that are about to print, once each.
	 */
	public function test_localize_scripts_only_for_queued_kudos_handles(): void {
		$front = $this->create_front();
		wp_scripts()->add_data( Front::SCRIPT_HANDLE_VIEW, 'data', '' );
		wp_scripts()->add_data( Front::SCRIPT_HANDLE_EDITOR, 'data', '' );

		$this->assertSame( [ 'jquery' ], $front->localize_scripts( [ 'jquery' ] ) );
		$this->assertStringNotContainsString( 'var kudos', (string) wp_scripts()->get_data( Front::SCRIPT_HANDLE_VIEW, 'data' ) );

		$front->localize_scripts( [ 'jquery', Front::SCRIPT_HANDLE_VIEW ] );
		$front->localize_scripts( [ Front::SCRIPT_HANDLE_VIEW ] );
		$data = (string) wp_scripts()->get_data( Front::SCRIPT_HANDLE_VIEW, 'data' );

		$this->assertSame( 1, substr_count( $data, 'var kudos = ' ) );
		$this->assertStringContainsString( 'restRoot', $data );
		$this->assertStringContainsString( '"countries"', $data );
		$this->assertStringNotContainsString( 'var kudos', (string) wp_scripts()->get_data( Front::SCRIPT_HANDLE_EDITOR, 'data' ) );
	}
}
