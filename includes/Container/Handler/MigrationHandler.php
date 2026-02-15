<?php
/**
 * Repository handler.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\NoticeService;

class MigrationHandler extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_DB_VERSION        = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY = '_kudos_migration_history';
	public const SETTING_PLUGIN_VERSION    = '_kudos_donations_version';

	/**
	 * Array of migrations.
	 *
	 * @var MigrationInterface[]
	 */
	protected array $migrations = [];

	/**
	 * MigrationManager constructor.
	 *
	 * @param iterable $migrations Migrations are injected here by the container.
	 */
	public function __construct( iterable $migrations ) {
		foreach ( $migrations as $migration ) {
			$this->add( $migration );
		}
		usort(
			$this->migrations,
			fn( MigrationInterface $a, MigrationInterface $b ) =>
			version_compare( $a->get_version(), $b->get_version() )
		);
	}

	/**
	 * Add service to list.
	 *
	 * @param MigrationInterface $migration Service.
	 */
	public function add( MigrationInterface $migration ): void {
		$this->migrations[] = $migration;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->add_localized_data();

		if ( $this->should_upgrade() ) {
			if ( ! Utils::is_kudos_admin() ) {
				$this->add_migration_notice();
			}
		}
	}

	/**
	 * Adds needsUpgrade to localized script data.
	 */
	private function add_localized_data(): void {
		add_filter(
			'kudos_global_localization',
			function ( array $data ): array {
				$data['needsUpgrade'] = $this->should_upgrade();
				return $data;
			}
		);
	}

	/**
	 * Determines if a migration should be run.
	 */
	public function should_upgrade(): bool {
		$db_version = get_option( self::SETTING_DB_VERSION );

		$current_version = ! empty( $db_version ) ? $db_version : '';

		$target_version = KUDOS_DB_VERSION;
		return $current_version && version_compare( $current_version, $target_version, '<' );
	}

	/**
	 * Returns currently stored migrations.
	 *
	 * @return MigrationInterface[]
	 */
	public function get_migrations(): array {
		return $this->migrations;
	}

	/**
	 * Returns migrations that have not yet been run.
	 *
	 * @return MigrationInterface[]
	 */
	public function get_pending_migrations(): array {
		$history = (array) get_option( self::SETTING_MIGRATION_HISTORY, [] );

		return array_filter(
			$this->migrations,
			fn( MigrationInterface $migration ) => ! \in_array( $migration->get_version(), $history, true )
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'admin_init';
	}

	/**
	 * Creates an admin notice with update button.
	 */
	private function add_migration_notice(): void {
		$form  = '<div>';
		$form .= "<a href='" . admin_url( '?page=kudos-campaigns' ) . "' class='button button-primary'>";
		$form .= __( 'Visit dashboard', 'kudos-donations' );
		$form .= '</a>';
		$form .= '</div>';

		NoticeService::notice(
			'<p><strong>' . __( 'Kudos Donations needs to update your database before you can continue.', 'kudos-donations' ) . '</strong><br/>' . __( 'Please make sure you backup your data before proceeding.', 'kudos-donations' ) . '</p>' . $form,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_MIGRATION_HISTORY => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
			self::SETTING_DB_VERSION        => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => KUDOS_DB_VERSION,
			],
			self::SETTING_PLUGIN_VERSION    => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => KUDOS_VERSION,
			],
		];
	}
}
