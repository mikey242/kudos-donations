<?php
/**
 * WPML (and Polylang) integration.
 *
 * Campaigns live in a custom table, so WPML cannot translate them as posts. Instead, their
 * visitor-facing text is registered with WPML String Translation and translated whenever
 * the donation form fetches the campaign. One campaign then serves every language, keeping
 * its totals and goal shared.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Helper\Localization;

class WpmlService extends AbstractRegistrable {

	/**
	 * Query argument the donation form sends with the campaign request. Deliberately not
	 * WPML's own `lang`, so admin requests (which must see the original text) are never
	 * translated by accident.
	 */
	public const QUERY_ARG = 'kudos_lang';

	/**
	 * Stores the plugin version the existing campaigns were last registered for.
	 */
	public const SETTING_STRINGS_REGISTERED = '_kudos_wpml_strings_registered';

	/**
	 * Campaign fields that donors see, in the order they appear in the form.
	 */
	public const TRANSLATABLE_FIELDS = [
		'initial_title',
		'initial_description',
		'subscription_title',
		'subscription_description',
		'address_title',
		'address_description',
		'message_title',
		'message_description',
		'payment_title',
		'payment_description',
		'return_message_title',
		'return_message_text',
		'terms_link',
		'privacy_link',
		'custom_return_url',
	];

	private CampaignRepository $campaign_repository;

	/**
	 * WpmlService constructor.
	 *
	 * @param CampaignRepository $campaign_repository Campaign repository.
	 */
	public function __construct( CampaignRepository $campaign_repository ) {
		$this->campaign_repository = $campaign_repository;
	}

	/**
	 * Only active alongside WPML or Polylang (which implements WPML's string API).
	 */
	public function is_enabled(): bool {
		return \defined( 'ICL_SITEPRESS_VERSION' ) || \defined( 'POLYLANG_VERSION' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_action( 'kudos_campaign_saved', [ $this, 'register_campaign_strings' ] );
		add_action( 'kudos_campaign_deleted', [ $this, 'unregister_campaign_strings' ] );
		add_action( 'admin_init', [ $this, 'maybe_register_existing_campaigns' ] );
		add_filter( 'kudos_rest_fields_campaign', [ $this, 'translate_rest_fields' ] );
		add_filter( Localization::FILTER_FRONT, [ $this, 'localize_language' ] );
	}

	/**
	 * The String Translation domain ("context") for a campaign's strings.
	 *
	 * @param int $campaign_id The campaign ID.
	 */
	public static function get_context( int $campaign_id ): string {
		return 'kudos-donations-campaign-' . $campaign_id;
	}

	/**
	 * Registers (or updates) a campaign's translatable strings.
	 *
	 * @param int $campaign_id The campaign ID.
	 */
	public function register_campaign_strings( int $campaign_id ): void {
		$campaign = $this->campaign_repository->get( $campaign_id );
		if ( $campaign instanceof CampaignEntity ) {
			$this->register_strings( $campaign );
		}
	}

	/**
	 * Removes a deleted campaign's strings from String Translation.
	 *
	 * @param int $campaign_id The campaign ID.
	 */
	public function unregister_campaign_strings( int $campaign_id ): void {
		if ( ! \function_exists( 'icl_unregister_string' ) ) {
			return;
		}
		foreach ( self::TRANSLATABLE_FIELDS as $field ) {
			icl_unregister_string( self::get_context( $campaign_id ), $field );
		}
	}

	/**
	 * Registers all existing campaigns once per plugin version, so campaigns created before
	 * WPML was activated (or before this integration existed) are translatable without
	 * having to re-save each one.
	 */
	public function maybe_register_existing_campaigns(): void {
		if ( get_option( self::SETTING_STRINGS_REGISTERED ) === KUDOS_VERSION ) {
			return;
		}

		foreach ( $this->campaign_repository->all() as $campaign ) {
			if ( $campaign instanceof CampaignEntity ) {
				$this->register_strings( $campaign );
			}
		}

		update_option( self::SETTING_STRINGS_REGISTERED, KUDOS_VERSION, false );
	}

	/**
	 * Translates the campaign REST response when the donation form asks for a language.
	 *
	 * @param array $data The prepared campaign data.
	 */
	public function translate_rest_fields( array $data ): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only endpoint.
		$language = isset( $_GET[ self::QUERY_ARG ] ) ? sanitize_key( wp_unslash( $_GET[ self::QUERY_ARG ] ) ) : '';

		if ( '' === $language || empty( $data['id'] ) ) {
			return $data;
		}

		return $this->translate( $data, (int) $data['id'], $language );
	}

	/**
	 * Translates the given campaign fields into a language. Untranslated strings keep their
	 * original value.
	 *
	 * @param array  $data        Campaign data keyed by field name.
	 * @param int    $campaign_id The campaign ID.
	 * @param string $language    WPML language code (e.g. "de").
	 */
	public function translate( array $data, int $campaign_id, string $language ): array {
		$context = self::get_context( $campaign_id );

		foreach ( self::TRANSLATABLE_FIELDS as $field ) {
			if ( ! isset( $data[ $field ] ) || ! \is_string( $data[ $field ] ) || '' === $data[ $field ] ) {
				continue;
			}
			$data[ $field ] = (string) apply_filters( 'wpml_translate_single_string', $data[ $field ], $context, $field, $language );
		}

		return $data;
	}

	/**
	 * Tells the donation form which language the current page is in.
	 *
	 * @param array $front Front-end localization data.
	 */
	public function localize_language( array $front ): array {
		$language = apply_filters( 'wpml_current_language', null );
		if ( \is_string( $language ) && '' !== $language ) {
			$front['language'] = $language;
		}
		return $front;
	}

	/**
	 * Registers a campaign's non-empty translatable fields.
	 *
	 * @param CampaignEntity $campaign The campaign.
	 */
	private function register_strings( CampaignEntity $campaign ): void {
		$context = self::get_context( (int) $campaign->id );

		$source_language = apply_filters( 'wpml_default_language', null );
		$source_language = \is_string( $source_language ) && '' !== $source_language ? $source_language : null;

		foreach ( self::TRANSLATABLE_FIELDS as $field ) {
			$value = $campaign->{$field} ?? null;
			if ( \is_string( $value ) && '' !== $value ) {
				do_action( 'wpml_register_single_string', $context, $field, $value, false, $source_language );
			}
		}
	}
}
