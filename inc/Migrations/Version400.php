<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Helpers\Blocks;
use IseardMedia\Kudos\Helpers\Settings;
use IseardMedia\Kudos\Helpers\WpDb;
use IseardMedia\Kudos\Service\AdminNotice;
use IseardMedia\Kudos\Service\MapperService;

class Version400 extends AbstractMigration implements MigrationInterface
{

    protected const VERSION = '400';
    /**
     * @var array
     */
    protected $campaigns;
    /**
     * @var \IseardMedia\Kudos\Service\MapperService
     */
    private $mapper;
    /**
     * @var array
     */
    private $transactions;
    /**
     * @var int[]|\WP_Post[]
     */
    private $posts;

    public function run()
    {
        $db                 = new WpDb();
        $this->mapper       = new MapperService($db);
        $this->posts        = get_posts([
            'numberposts' => -1,
            'post_type'   => 'any',
        ]);
        $this->campaigns    = [];
        $this->transactions = [];

        $this->migrate_campaigns();
        $this->migrate_transactions();
        $this->migrate_blocks();
        $this->migrate_smtp();
        $this->logger->info('Migration 400 complete');
        new AdminNotice(
            'Database update complete. <br/> Notice: If you have used [kudos] shortcodes, you will need to re-add them.',
            'warning'
        );
    }

    /**
     * @return void
     */
    public function migrate_campaigns()
    {
        $old_campaigns = Settings::get_setting('campaigns');
        if ($old_campaigns) {
            foreach ($old_campaigns as $old_campaign) {
                $new_id = wp_insert_post([
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
                        'theme_color'           => Settings::get_setting('theme_colors')['primary'],
                        'terms_link'            => Settings::get_setting('terms_link'),
                        'privacy_link'          => Settings::get_setting('privacy_link'),
                        'show_return_message'   => Settings::get_setting('return_message_enable'),
                        'use_custom_return_url' => Settings::get_setting('custom_return_enable'),
                        'custom_return_url'     => Settings::get_setting('custom_return_url'),
                        'return_message_title'  => Settings::get_setting('return_message_title'),
                        'return_message_text'   => Settings::get_setting('return_message_text'),
                    ],
                ]);
                if ($new_id) {
                    // Add fixed amounts separately as they are multiple values for the same key.
                    if ( ! empty($old_campaign['fixed_amounts'])) {
                        $old_fixed_amounts = str_replace(' ', '', $old_campaign['fixed_amounts']);
                        $fixed_amounts     = explode(",", $old_fixed_amounts);
                        foreach ($fixed_amounts as $amount) {
                            add_post_meta($new_id, 'fixed_amounts', $amount);
                        }
                    }

                    $this->campaigns[$old_campaign['id']] = $new_id;
                }
            }

            Settings::remove_setting('campaigns');

            $this->logger->info("Migrated campaign(s)", $this->campaigns);

            return;
        }
        $this->logger->info(__('No old campaigns found', 'kudos-donations'));
    }

    public function migrate_transactions()
    {
        if ( ! empty($this->campaigns)) {
            $campaigns          = $this->campaigns;
            $this->transactions = [];
            $mapper             = $this->mapper->get_repository(TransactionEntity::class);


            $transactions = $mapper->get_all_by();

            /** @var TransactionEntity $transaction */
            foreach ($transactions as $transaction) {
                if ($transaction->campaign_id && $campaigns[$transaction->campaign_id]) {
                    $transaction->set_fields([
                        'campaign_id' => $campaigns[$transaction->campaign_id],
                    ]);
                    $mapper->save($transaction);
                    $this->transactions[] = $transaction;
                }
            }

            $this->logger->info("Migrated transaction(s)", $this->transactions);
        }
    }

    private function migrate_blocks()
    {
        if ( ! empty($this->campaigns)) {
            foreach ($this->posts as $post) {
                $new_content = Blocks::getNewContent($post->ID, function ($block) {
                    if (isset($block['blockName']) && $block['blockName'] === 'iseardmedia/kudos-button') {
                        $old_id                        = $block['attrs']['campaign_id'] ?? 'default';
                        $block['attrs']['campaign_id'] = (string)$this->campaigns[$old_id];
                    }

                    return $block;
                });

                $post = [
                    'ID'           => $post->ID,
                    'post_content' => $new_content,
                ];

                wp_update_post($post);
            }
        }
    }

    private function migrate_smtp()
    {
        $from       = Settings::get_setting('smtp_from');
        $host       = Settings::get_setting('smtp_host');
        $port       = Settings::get_setting('smtp_port');
        $encryption = Settings::get_setting('smtp_encryption');
        $autotls    = Settings::get_setting('smtp_autotls');
        $username   = Settings::get_setting('smtp_username');
        $password   = Settings::get_setting('smtp_password');

        Settings::update_array('custom_smtp', [
            'from_email' => $from,
            'host'       => $host,
            'port'       => $port,
            'encryption' => $encryption,
            'autotls'    => $autotls,
            'username'   => $username,
            'password'   => $password,
        ]);
    }
}