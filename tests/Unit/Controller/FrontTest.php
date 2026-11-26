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
		$html           = $front->render_form( $args );
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

	/**
	 * The options in the rendered form's data-options attribute.
	 *
	 * @param string $html Output of render_form().
	 */
	private function get_rendered_options( string $html ): array {
		preg_match( '/data-options="([^"]*)"/', $html, $matches );
		return json_decode( html_entity_decode( $matches[1] ?? '' ), true ) ?? [];
	}

	public function test_render_uses_default_button_label(): void {
		add_filter(
			'gettext_kudos-donations',
			static fn( $translation, $text ) => 'Donate now' === $text ? 'Doneer nu' : $translation,
			10,
			2
		);

		$empty   = $this->get_rendered_options( (string) $this->create_front()->render_form( [ 'campaign_id' => 1, 'button_label' => '' ] ) );
		$missing = $this->get_rendered_options( (string) $this->create_front()->render_form( [ 'campaign_id' => 1 ] ) );

		remove_all_filters( 'gettext_kudos-donations' );
		$this->assertSame( 'Doneer nu', $empty['button_label'] );
		$this->assertSame( 'Doneer nu', $missing['button_label'] );
	}

	public function test_render_keeps_custom_button_label(): void {
		$options = $this->get_rendered_options( (string) $this->create_front()->render_form( [ 'campaign_id' => 1, 'button_label' => 'Give today' ] ) );

		$this->assertSame( 'Give today', $options['button_label'] );
	}

	public function test_render_records_placement_url_on_public_page(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'publish' ] );
		$this->go_to( get_permalink( $page_id ) . '?utm_source=test' );

		$this->create_front()->render_form( [ 'campaign_id' => 1 ] );

		$this->assertTrue( Front::is_form_placed() );
		$this->assertSame( get_permalink( $page_id ), get_option( Front::SETTING_FORM_PLACED_URL ) );
	}

	public function test_render_keeps_first_placement_url(): void {
		update_option( Front::SETTING_FORM_PLACED_URL, 'https://example.org/first/' );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'publish' ] );
		$this->go_to( get_permalink( $page_id ) );

		$this->create_front()->render_form( [ 'campaign_id' => 1 ] );

		$this->assertSame( 'https://example.org/first/', get_option( Front::SETTING_FORM_PLACED_URL ) );
	}

	public function test_render_does_not_record_in_preview(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'draft' ] );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->go_to( add_query_arg( [ 'page_id' => $page_id, 'preview' => 'true' ], home_url( '/' ) ) );

		$this->create_front()->render_form( [ 'campaign_id' => 1 ] );

		$this->assertFalse( Front::is_form_placed() );
	}

	public function test_render_does_not_record_in_admin(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		set_current_screen( 'dashboard' );

		$this->create_front()->render_form( [ 'campaign_id' => 1 ] );

		set_current_screen( 'front' );
		$this->assertFalse( Front::is_form_placed() );
	}

	public function test_render_does_not_record_without_campaign(): void {
		delete_option( Front::SETTING_FORM_PLACED_URL );
		$this->go_to( home_url( '/' ) );

		$this->create_front()->render_form( [ 'campaign_id' => '' ] );

		$this->assertFalse( Front::is_form_placed() );
	}

	/**
	 * Reads a data attribute from rendered HTML, decoding entities as a browser would.
	 *
	 * @param string $html      The HTML.
	 * @param string $attribute The attribute name.
	 */
	private function get_attribute( string $html, string $attribute ): ?string {
		$processor = new \WP_HTML_Tag_Processor( $html );
		while ( $processor->next_tag() ) {
			$value = $processor->get_attribute( $attribute );
			if ( \is_string( $value ) ) {
				return $value;
			}
		}
		return null;
	}

	/**
	 * A button label with quotes survives into data-options as valid JSON.
	 */
	public function test_render_keeps_options_intact_with_quotes_in_label(): void {
		$args = [
			'campaign_id'  => 291,
			'button_label' => 'Support St John\'s "Appeal" & more',
			'type'         => 'button',
		];

		$html = (string) $this->create_front()->render_form( $args );

		$this->assertSame( $args, json_decode( (string) $this->get_attribute( $html, 'data-options' ), true ), $html );
	}

	/**
	 * Rendering a form enqueues the front-end assets, so add-ons need not.
	 */
	public function test_render_form_enqueues_assets(): void {
		wp_dequeue_script( Front::SCRIPT_HANDLE_VIEW );
		wp_dequeue_style( Front::STYLE_HANDLE_VIEW );

		$this->create_front()->render_form( [ 'campaign_id' => 1 ] );

		$this->assertTrue( wp_script_is( Front::SCRIPT_HANDLE_VIEW ) );
		$this->assertTrue( wp_style_is( Front::STYLE_HANDLE_VIEW ) );
	}

	/**
	 * Nothing is enqueued when no form is output.
	 */
	public function test_render_form_without_campaign_enqueues_nothing(): void {
		wp_dequeue_script( Front::SCRIPT_HANDLE_VIEW );
		wp_dequeue_style( Front::STYLE_HANDLE_VIEW );

		$this->assertNull( $this->create_front()->render_form( [ 'campaign_id' => '' ] ) );

		$this->assertFalse( wp_script_is( Front::SCRIPT_HANDLE_VIEW ) );
		$this->assertFalse( wp_style_is( Front::STYLE_HANDLE_VIEW ) );
	}

	/**
	 * A message with an apostrophe (e.g. a translation) keeps its full title and body.
	 */
	public function test_message_modal_keeps_title_with_apostrophe(): void {
		$message = 'L\'abonnement a été annulé';
		add_filter(
			'rest_pre_dispatch',
			static fn() => new \WP_REST_Response( [ 'message' => $message ], 200 )
		);
		$_REQUEST['kudos_action'] = 'cancel_subscription';

		ob_start();
		$this->create_front()->handle_query_variables();
		$html = (string) ob_get_clean();
		unset( $_REQUEST['kudos_action'] );

		$this->assertSame( $message, $this->get_attribute( $html, 'data-title' ), $html );
		$this->assertSame( 'Thanks for your support!', $this->get_attribute( $html, 'data-body' ), $html );
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
