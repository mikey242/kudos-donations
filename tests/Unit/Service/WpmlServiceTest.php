<?php
/**
 * WpmlService tests.
 *
 * WPML is not installed in the test site, so its string API is stood in for with filters
 * and actions that record what the service sends.
 */

namespace IseardMedia\Kudos\Tests\Service;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Service\WpmlService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Service\WpmlService
 */
class WpmlServiceTest extends BaseTestCase {

	private CampaignRepository $campaign_repository;
	private WpmlService $wpml;

	/**
	 * Strings registered through `wpml_register_single_string`, keyed "context|name".
	 *
	 * @var array<string, string>
	 */
	private array $registered = [];

	public function set_up(): void {
		parent::set_up();
		$this->campaign_repository = $this->get_from_container( CampaignRepository::class );
		$this->wpml                = $this->get_from_container( WpmlService::class );

		$this->registered = [];
		add_action(
			'wpml_register_single_string',
			function ( $context, $name, $value ): void {
				$this->registered[ $context . '|' . $name ] = $value;
			},
			10,
			3
		);

		// Fake translations: German gets a prefix, every other language falls through.
		add_filter(
			'wpml_translate_single_string',
			static fn( $value, $context, $name, $language ) => 'de' === $language ? 'DE: ' . $value : $value,
			10,
			4
		);
	}

	public function tear_down(): void {
		remove_all_actions( 'wpml_register_single_string' );
		remove_all_filters( 'wpml_translate_single_string' );
		remove_all_actions( 'kudos_campaign_saved' );
		remove_all_actions( 'kudos_campaign_deleted' );
		delete_option( WpmlService::SETTING_STRINGS_REGISTERED );
		unset( $_GET[ WpmlService::QUERY_ARG ] );
		parent::tear_down();
	}

	/**
	 * Saving a campaign through the repository registers its strings via `kudos_campaign_saved`.
	 */
	public function test_campaign_save_hook_reaches_service(): void {
		add_action( 'kudos_campaign_saved', [ $this->wpml, 'register_campaign_strings' ] );

		$id = (int) $this->campaign_repository->insert( new CampaignEntity( [ 'initial_title' => 'Hooked' ] ) );

		$this->assertSame( 'Hooked', $this->registered[ WpmlService::get_context( $id ) . '|initial_title' ] );
	}

	/**
	 * Saving a campaign registers its non-empty, visitor-facing text.
	 */
	public function test_registers_campaign_strings(): void {
		$id = (int) $this->campaign_repository->insert(
			new CampaignEntity(
				[
					'title'         => 'Internal name',
					'initial_title' => 'Support us',
					'terms_link'    => '',
				]
			)
		);

		$this->wpml->register_campaign_strings( $id );

		$context = WpmlService::get_context( $id );
		$this->assertSame( 'Support us', $this->registered[ $context . '|initial_title' ] );
		$this->assertArrayNotHasKey( $context . '|terms_link', $this->registered );
		// The admin-only title is not exposed for translation.
		$this->assertArrayNotHasKey( $context . '|title', $this->registered );
	}

	public function test_registers_strings_in_default_language(): void {
		$languages = [];
		add_action(
			'wpml_register_single_string',
			static function ( $context, $name, $value, $allow_empty = false, $source_language = null ) use ( &$languages ): void {
				$languages[ $name ] = $source_language;
			},
			10,
			5
		);
		add_filter( 'wpml_default_language', static fn() => 'nl' );

		$id = (int) $this->campaign_repository->insert( new CampaignEntity( [ 'initial_title' => 'Steun ons' ] ) );
		$this->wpml->register_campaign_strings( $id );

		remove_all_filters( 'wpml_default_language' );
		$this->assertSame( 'nl', $languages['initial_title'] );
	}

	/**
	 * Existing campaigns are registered once per plugin version.
	 */
	public function test_registers_existing_campaigns_once_per_version(): void {
		$id = (int) $this->campaign_repository->insert( new CampaignEntity( [ 'initial_title' => 'Existing' ] ) );

		$this->wpml->maybe_register_existing_campaigns();
		$this->assertSame( 'Existing', $this->registered[ WpmlService::get_context( $id ) . '|initial_title' ] );
		$this->assertSame( KUDOS_VERSION, get_option( WpmlService::SETTING_STRINGS_REGISTERED ) );

		$this->registered = [];
		$this->wpml->maybe_register_existing_campaigns();
		$this->assertSame( [], $this->registered );
	}

	/**
	 * The REST response is translated only when the form asks for a language.
	 */
	public function test_translates_rest_fields_only_with_language_arg(): void {
		$data = [
			'id'            => 7,
			'title'         => 'Internal name',
			'initial_title' => 'Support us',
			'goal'          => 100.0,
		];

		$this->assertSame( $data, $this->wpml->translate_rest_fields( $data ) );

		$_GET[ WpmlService::QUERY_ARG ] = 'de';
		$translated                     = $this->wpml->translate_rest_fields( $data );

		$this->assertSame( 'DE: Support us', $translated['initial_title'] );
		$this->assertSame( 'Internal name', $translated['title'] );
		$this->assertSame( 100.0, $translated['goal'] );
	}

	/**
	 * Untranslated languages keep the original text.
	 */
	public function test_untranslated_language_keeps_original(): void {
		$translated = $this->wpml->translate( [ 'initial_title' => 'Support us' ], 7, 'fr' );

		$this->assertSame( 'Support us', $translated['initial_title'] );
	}

	/**
	 * The page language reaches the front end so the form can send it.
	 */
	public function test_localizes_current_language(): void {
		$this->assertSame( [], $this->wpml->localize_language( [] ) );

		add_filter( 'wpml_current_language', static fn() => 'de' );
		$this->assertSame( [ 'language' => 'de' ], $this->wpml->localize_language( [] ) );
		remove_all_filters( 'wpml_current_language' );
	}
}
