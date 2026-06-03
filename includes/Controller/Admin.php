<?php
/**
 * Admin related functions.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\LinkService;

class Admin extends AbstractRegistrable {

	private LinkService $link_service;
	private CampaignRepository $campaign_repository;
	private TransactionRepository $transaction_repository;

	/**
	 * @param LinkService           $link_service The linking service.
	 * @param CampaignRepository    $campaign_repository The campaign repository.
	 * @param TransactionRepository $transaction_repository The transaction repository.
	 */
	public function __construct( LinkService $link_service, CampaignRepository $campaign_repository, TransactionRepository $transaction_repository ) {
		$this->link_service           = $link_service;
		$this->campaign_repository    = $campaign_repository;
		$this->transaction_repository = $transaction_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'admin_init';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->maybe_enable_debug_mode();
		$this->handle_query_variables();
	}

	/**
	 * Enables debug mode when the kudos_debug GET parameter is present.
	 */
	private function maybe_enable_debug_mode(): void {
        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['kudos_debug'] ) && current_user_can( 'manage_options' ) ) {
			update_option( '_kudos_debug_mode', true );
			wp_safe_redirect( remove_query_arg( 'kudos_debug' ) );
			exit;
		}
	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables(): void {
		if ( isset( $_REQUEST['kudos_action'] ) ) {
			$action = sanitize_text_field( wp_unslash( (string) $_REQUEST['kudos_action'] ) );
			$this->logger->debug( 'Action requested', [ 'action' => $action ] );
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

			switch ( $action ) {
				case 'kudos_clear_settings':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_settings' ) ) {
						global $new_allowed_options;
						$settings = $new_allowed_options['kudos-donations'];
						foreach ( $settings as $setting_name ) {
							delete_option( $setting_name );
						}
					}
					break;
				case 'kudos_clear_campaigns':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_campaigns' ) ) {
						$campaigns = $this->campaign_repository->all();
						foreach ( $campaigns as $campaign ) {
							$this->campaign_repository->delete( $campaign->id );
						}
					}
					break;
				case 'kudos_clear_twig_cache':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_twig_cache' ) ) {
						CacheService::recursively_clear_cache( 'twig' );
					}
					break;
				case 'kudos_clear_container_cache':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_container_cache' ) ) {
						CacheService::recursively_clear_cache( 'container' );
					}
					break;
				case 'kudos_clear_all_cache':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_all_cache' ) ) {
						CacheService::recursively_clear_cache();
						do_action( 'kudos_clear_all_cache' );
					}
					break;
				case 'kudos_assign_transactions_to_campaign':
					if ( wp_verify_nonce( $nonce, 'kudos_assign_transactions_to_campaign' ) ) {
						$from = isset( $_POST['kudos_from_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_from_campaign'] ) ) : '';
						$to   = isset( $_POST['kudos_to_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_to_campaign'] ) ) : '';

						switch ( $from ) {
							case '_orphaned_transactions_':
								$transactions = $this->transaction_repository->get_orphan_ids();
								break;
							case '_all_transactions_':
								$transactions = array_map(
									fn( TransactionEntity $t ): int => $t->id,
									$this->transaction_repository->all()
								);
								break;
							default:
								$transactions = array_map(
									fn( $t ) => $t->id,
									$this->transaction_repository->find_by(
										[
											'campaign_id' => $from,
										]
									)
								);
								break;
						}

						$this->logger->info(
							\sprintf( 'Assigning %s transactions to campaign', \count( $transactions ) ),
							[
								'from' => $from,
								'to'   => $to,
							]
						);

						foreach ( $transactions as $transaction ) {
							$this->transaction_repository->patch( $transaction, [ 'campaign_id' => $to ] );
						}
					}
					break;
				case 'kudos_update_migration_history':
					if ( wp_verify_nonce( $nonce, 'kudos_update_migration_history' ) ) {
						$history = isset( $_POST['kudos_migration_history'] ) && \is_array( $_POST['kudos_migration_history'] )
							? array_map( 'sanitize_text_field', wp_unslash( $_POST['kudos_migration_history'] ) )
							: [];
						update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, $history );

						$db_version = isset( $_POST['kudos_db_version'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_db_version'] ) ) : '';
						update_option( MigrationHandler::SETTING_DB_VERSION, $db_version );
					}
					break;
				case 'kudos_link_entities':
					if ( wp_verify_nonce( $nonce, 'kudos_link_entities' ) ) {
						$source_repo_class = isset( $_POST['kudos_source_repo'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_source_repo'] ) ) : null;
						$local_key         = isset( $_POST['kudos_local_key'] ) ? sanitize_text_field( $_POST['kudos_local_key'] ) : null;
						$vendor_key        = isset( $_POST['kudos_vendor_key'] ) ? sanitize_text_field( $_POST['kudos_vendor_key'] ) : null;
						$target_repo_class = isset( $_POST['kudos_target_repo'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_target_repo'] ) ) : null;
						$target_vendor_key = isset( $_POST['kudos_target_vendor_key'] ) ? sanitize_text_field( $_POST['kudos_target_vendor_key'] ) : null;

						$this->link_service->link_entities( $source_repo_class, $local_key, $vendor_key, $target_repo_class, $target_vendor_key );

					}
					break;
				default:
					$this->logger->debug( 'Action not implemented', [ 'action' => $action ] );
					break;
			}
		}
	}
}
