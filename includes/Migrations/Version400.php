<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Admin\Notice\AdminDismissibleNotice;
use IseardMedia\Kudos\Helper\Blocks;
use IseardMedia\Kudos\Service\SettingsService;
use WP_Post;

class Version400 extends AbstractMigration implements MigrationInterface {


	protected const VERSION = '400';
	/**
	 * @var array
	 */
	protected $campaigns;
	/**
	 * @var array
	 */
	private $transactions;
	/**
	 * @var int[]|WP_Post[]
	 */
	private $posts;

	public function run(): void {
		$this->posts        = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => 'any',
			]
		);
		$this->campaigns    = [];
		$this->transactions = [];

		$this->migrate_campaigns();
		$this->migrate_transactions();
		$this->migrate_blocks();
		$this->migrate_smtp();
		$this->logger->info( 'Migration 400 complete' );
		( new AdminDismissibleNotice() )->warning( 'Database update complete. <br/> Notice: If you have used [kudos] shortcodes, you will need to re-add them.', );
	}

	/**
	 */
	public function migrate_campaigns(): void {
		$old_campaigns = $this->settings->get_setting( 'campaigns' );
		if ( $old_campaigns ) {
			foreach ( $old_campaigns as $old_campaign ) {
				$new_id = wp_insert_post(
					[
						'post_status' => 'publish',
						'post_type'   => 'kudos_campaign',
						'post_title'  => $old_campaign['name'],
						'meta_input'  => [
							'goal'                  => $old_campaign['campaign_goal'] ?? '',
							'show_goal'             => $old_campaign['show_progress'] ?? false,
							'additional_funds'      => $old_campaign['additional_funds'] ?? null,
							'initial_title'         => $old_campaign['modal_title'] ?? '',
							'initial_description'   => $old_campaign['welcome_text'] ?? '',
							'address_enabled'       => $old_campaign['address_enabled'] ?? false,
							'address_required'      => $old_campaign['address_required'] ?? false,
							'message_enabled'       => $old_campaign['message_enabled'] ?? false,
							'amount_type'           => $old_campaign['amount_type'] ?? 'both',
							'donation_type'         => $old_campaign['donation_type'] ?? 'oneoff',
							'minimum_donation'      => 1,
							'theme_color'           => $this->settings->get_setting( '_kudos_theme_colors' )['primary'],
							'terms_link'            => $this->settings->get_setting( '_kudos_terms_link' ),
							'privacy_link'          => $this->settings->get_setting( '_kudos_privacy_link' ),
							'show_return_message'   => $this->settings->get_setting( '_kudos_return_message_enable' ),
							'use_custom_return_url' => $this->settings->get_setting( '_kudos_custom_return_enable' ),
							'custom_return_url'     => $this->settings->get_setting( '_kudos_custom_return_url' ),
							'return_message_title'  => $this->settings->get_setting( '_kudos_return_message_title' ),
							'return_message_text'   => $this->settings->get_setting( '_kudos_return_message_text' ),
						],
					]
				);
				if ( $new_id ) {
					// Add fixed amounts separately as they are multiple values for the same key.
					if ( ! empty( $old_campaign['fixed_amounts'] ) ) {
						$old_fixed_amounts = str_replace( ' ', '', $old_campaign['fixed_amounts'] );
						$fixed_amounts     = explode( ',', $old_fixed_amounts );
						foreach ( $fixed_amounts as $amount ) {
							add_post_meta( $new_id, 'fixed_amounts', $amount );
						}
					}

					$this->campaigns[ $old_campaign['id'] ] = $new_id;
				}
			}

			delete_option( '_kudos_campaigns' );

			$this->logger->info( 'Migrated campaign(s)', $this->campaigns );

			return;
		}
		$this->logger->info( __( 'No old campaigns found', 'kudos-donations' ) );
	}

	public function migrate_transactions(): void {
		if ( ! empty( $this->campaigns ) ) {
			$campaigns          = $this->campaigns;
			$this->transactions = [];
			$mapper             = $this->mapper->get_repository( TransactionEntity::class );

			$transactions = $mapper->get_all_by();

			/** @var TransactionEntity $transaction */
			foreach ( $transactions as $transaction ) {
				if ( $transaction->campaign_id && $campaigns[ $transaction->campaign_id ] ) {
					$transaction->set_fields(
						[
							'campaign_id' => $campaigns[ $transaction->campaign_id ],
						]
					);
					$mapper->save( $transaction );
					$this->transactions[] = $transaction;
				}
			}

			$this->logger->info( 'Migrated transaction(s)', $this->transactions );
		}
	}

	private function migrate_blocks(): void {
		if ( ! empty( $this->campaigns ) ) {
			foreach ( $this->posts as $post ) {
				$new_content = Blocks::getNewContent(
					$post->ID,
					function ( $block ) {
						if ( isset( $block['blockName'] ) && $block['blockName'] === 'iseardmedia/kudos-button' ) {
							$old_id                        = $block['attrs']['campaign_id'] ?? 'default';
							$block['attrs']['campaign_id'] = (string) $this->campaigns[ $old_id ];
						}

						return $block;
					}
				);

				$post = [
					'ID'           => $post->ID,
					'post_content' => $new_content,
				];

				wp_update_post( $post );
			}
		}
	}

	private function migrate_smtp(): void {
		$from       = $this->settings->get_setting( '_kudos_smtp_from' );
		$host       = $this->settings->get_setting( '_kudos_smtp_host' );
		$port       = $this->settings->get_setting( '_kudos_smtp_port' );
		$encryption = $this->settings->get_setting( '_kudos_smtp_encryption' );
		$autotls    = $this->settings->get_setting( '_kudos_smtp_autotls' );
		$username   = $this->settings->get_setting( '_kudos_smtp_username' );
		$password   = $this->settings->get_setting( '_kudos_smtp_password' );

		$this->settings->update_setting(
			SettingsService::SETTING_NAME_CUSTOM_SMTP,
			[
				'from_email' => $from,
				'host'       => $host,
				'port'       => $port,
				'encryption' => $encryption,
				'autotls'    => $autotls,
				'username'   => $username,
				'password'   => $password,
			]
		);
	}
}
