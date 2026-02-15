<?php
/**
 * Admin related functions.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Admin\DebugAdminPage;
use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\LinkService;
use IseardMedia\Kudos\Service\NoticeService;

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
		$this->handle_query_variables();
		$this->show_notices();
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
							wp_delete_post( $campaign->id, true );
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
					}
					break;
				case 'kudos_clear_logs':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_logs' ) ) {
						$log_files = DebugAdminPage::get_logs();
						foreach ( $log_files as $log_file ) {
							wp_delete_file( $log_file );
						}
					}
					break;
				case 'kudos_assign_transactions_to_campaign':
					if ( wp_verify_nonce( $nonce, 'kudos_assign_transactions_to_campaign' ) ) {
						$from             = isset( $_POST['kudos_from_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_from_campaign'] ) ) : '';
						$to               = isset( $_POST['kudos_to_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['kudos_to_campaign'] ) ) : '';
						$transaction_repo = $this->transaction_repository;

						switch ( $from ) {
							case '_orphaned_transactions_':
								$transactions = $this->get_orphan_transaction_ids();
								break;
							case '_all_transactions_':
								$transactions = array_map(
									fn( TransactionEntity $t ): int => $t->id,
									$transaction_repo->all()
								);
								break;
							default:
								$transactions = array_map(
									fn( $t ) => $t->id,
									$transaction_repo->find_by(
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
							$transaction_repo->patch( $transaction, [ 'campaign_id' => $to ] );
						}
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

	/**
	 * Shows admin notices stored in the option.
	 */
	public function show_notices(): void {
		$notices = NoticeService::get_notices();
		if ( $notices ) {
			add_action(
				'admin_print_footer_scripts',
				function () {
					echo '
					<script type="text/javascript">
						const notices = document.querySelectorAll(".kudos-notice")
						notices.forEach((notice) => {
                            notice.addEventListener("click", function() {
                                const key = notice.dataset.noticeKey;
                                fetch("' . esc_url( rest_url( '/kudos/v1/notice/dismiss' ) ) . '", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                		"X-WP-Nonce": "' . esc_attr( wp_create_nonce( 'wp_rest' ) ) . '"
                                    },
                                    body: JSON.stringify({ id: key })
                                })
                            })
						})
					</script>
				';
				}
			);
			foreach ( $notices as $key => $notice ) {
				$message     = $notice['message'] ?? $notice;
				$level       = $notice['level'] ?? NoticeService::INFO;
				$dismissible = $notice['dismissible'] ?? true;
				$logo        = $notice['logo'] ?? true;
				NoticeService::notice(
					$message,
					$level,
					$dismissible,
					(string) $key,
					$logo
				);
			}
		}
	}

	/**
	 * Gets a list of transactions with no Campaign.
	 *
	 * @return list<int>
	 */
	private function get_orphan_transaction_ids(): array {
		/** @var TransactionEntity[] $transactions */
		$transactions           = $this->transaction_repository->all();
		$orphan_transaction_ids = [];

		foreach ( $transactions as $transaction ) {
			$campaign_id = $transaction->campaign_id;

			$is_missing = empty( $campaign_id ) || ! $this->campaign_repository->get( $campaign_id );

			if ( $is_missing ) {
				$orphan_transaction_ids[] = $transaction->id;
			}
		}

		return $orphan_transaction_ids;
	}
}
