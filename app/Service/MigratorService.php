<?php

namespace Kudos\Service;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;

class MigratorService
{
    /**
     * @var \Kudos\Service\MapperService
     */
    private $mapper;

    public function __construct(MapperService $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return void
     */
    public function migrate_campaigns()
    {
        $old_campaigns = Settings::get_setting('campaigns');
        if ($old_campaigns) {
            $success = 0;
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
                        $fixed_amounts = explode(",", $old_campaign['fixed_amounts']);
                        foreach ($fixed_amounts as $amount) {
                            add_post_meta($new_id, 'fixed_amounts', $amount);
                        }
                    }

                    // Assign transactions to new campaign id.
                    $transactions = $this->mapper->get_repository(TransactionEntity::class)
                                                 ->get_all_by(['campaign_id' => $old_campaign['id']]);
                    /** @var TransactionEntity $transaction */
                    foreach ($transactions as $transaction) {
                        $transaction->set_fields([
                            'campaign_id' => $new_id,
                        ]);
                        $this->mapper->save($transaction);
                    }
                    $success++;
                }
            }
            new AdminNotice(
                sprintf(
                /* translators: %s: Number of records. */
                    _n(
                        'Migrated %s campaign',
                        'Migrated %s campaigns',
                        $success,
                        'kudos-donations'
                    ),
                    $success
                )
            );

            return;
        }
        new AdminNotice(__('No old campaigns found', 'kudos-donations'));
    }
}