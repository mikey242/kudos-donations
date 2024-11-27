<?php
/**
 * Newsletter service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\Services;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\KudosPremium\NewsletterProvider\NewsletterProviderInterface;

class NewsletterService extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_NEWSLETTER_PROVIDER = '_kudos_newsletter_provider';
	public const SETTING_CHECKBOX_TEXT       = '_kudos_newsletter_checkbox_text';

	public ?NewsletterProviderInterface $newsletter_provider;

	/**
	 * Newsletter service constructor.
	 *
	 * @param ?NewsletterProviderInterface $newsletter_provider The newsletter provider class.
	 */
	public function __construct( ?NewsletterProviderInterface $newsletter_provider ) {
		$this->newsletter_provider = $newsletter_provider;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_filter( 'kudos_campaign_rest_fields', [ $this, 'add_rest_fields_to_campaign' ] );
		add_action( 'kudos_submit_payment', [ $this, 'schedule_subscribe_user' ] );
		add_action( 'subscribe_user', [ $this, 'subscribe_user' ] );
	}

	/**
	 * Add the required rest fields to the campaign rest response.
	 *
	 * @param array $fields The rest fields.
	 */
	public function add_rest_fields_to_campaign( array $fields ): array {
		$fields['newsletter'] = [
			'get_callback' => fn() =>
			[
				'enabled'       => get_option( self::SETTING_NEWSLETTER_PROVIDER ) !== 'none',
				'checkbox_text' => get_option( self::SETTING_CHECKBOX_TEXT, __( 'I would like to subscribe to the newsletter', 'kudos-donations' ) ),
			],

		];
		return $fields;
	}

	/**
	 * Schedules subscribing the user to the newsletter.
	 *
	 * @param array $fields The form fields.
	 */
	public function schedule_subscribe_user( array $fields ): void {
		if ( isset( $fields['newsletter'] ) && 'true' === $fields['newsletter'] ) {
			Utils::schedule_action( strtotime( '+1 minute' ), 'subscribe_user', [ $fields ] );
		}
	}

	/**
	 * Subscribe the user with the selected newsletter provider.
	 *
	 * @param array $fields The raw form fields.
	 */
	public function subscribe_user( array $fields ): void {
		$this->newsletter_provider->subscribe_user( $fields['email'], $fields['name'] ?? null );
	}

	/**
	 * Must be lower than the rest field registration priority.
	 */
	public static function get_registration_action_priority(): int {
		return 5;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_NEWSLETTER_PROVIDER => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'none',
			],
			self::SETTING_CHECKBOX_TEXT       => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => __( 'I would like to subscribe to the newsletter', 'kudos-donations' ),
			],
		];
	}
}
