<?php

namespace Kudos\Controller;

use Kudos\Controller\Table\DonorsTable;
use Kudos\Controller\Table\SubscriptionsTable;
use Kudos\Controller\Table\TransactionsTable;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Assets;
use Kudos\Helpers\Settings;
use Kudos\Service\ActivatorService;
use Kudos\Service\AdminNotice;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\MigratorService;
use Kudos\Service\PaymentService;
use Kudos\Service\TwigService;
use Kudos\Service\Vendor\MollieVendor;

class Admin
{
    /**
     * The version of this plugin.
     *
     * @var string $version The current version of this plugin.
     */
    private $version;
    /**
     * @var MapperService
     */
    private $mapper;
    /**
     * @var TransactionsTable
     */
    private $table;
    /**
     * @var TwigService
     */
    private $twig;
    /**
     * @var PaymentService
     */
    private $payment;
    /**
     * @var ActivatorService
     */
    private $activator;
    /**
     * @var \Kudos\Service\Vendor\MollieVendor
     */
    private $mollie;
    /**
     * @var \Kudos\Service\LoggerService
     */
    private $logger;
    /**
     * @var \Kudos\Service\MigratorService
     */
    private $migrator;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $version The version of this plugin.
     */
    public function __construct(
        string $version,
        MapperService $mapper,
        TwigService $twig,
        PaymentService $payment,
        ActivatorService $activator,
        MollieVendor $mollie_vendor,
        MigratorService $migrator,
        LoggerService $logger
    ) {
        $this->version   = $version;
        $this->mapper    = $mapper;
        $this->twig      = $twig;
        $this->payment   = $payment;
        $this->activator = $activator;
        $this->mollie    = $mollie_vendor;
        $this->logger    = $logger;
        $this->migrator  = $migrator;
    }

    public function check_migration_actions()
    {
        $actions = Settings::get_setting('migration_actions');
        if ($actions) {
            foreach ($actions as $action) {
                switch ($action) {
                    case 'migrate_campaigns':
                        $this->migrator->migrate_campaigns();
                        break;
                }
                if (($key = array_search($action, $actions)) !== false) {
                    unset($actions[$key]);
                }
                Settings::update_setting('migration_actions', $actions);
            }
        }
    }

    /**
     * Create the Kudos Donations admin pages.
     */
    public function add_menu_pages()
    {
        $parent_slug = apply_filters('kudos_parent_settings_slug', 'kudos-campaigns');

        add_menu_page(
            __('Kudos', 'kudos-donations'),
            __('Donations', 'kudos-donations'),
            'manage_options',
            $parent_slug,
            false,
            'data:image/svg+xml;base64,' . base64_encode(
                '<svg viewBox="0 0 555 449" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2"><path fill="#f0f5fa99" d="M0 65.107a65.114 65.114 0 0 1 19.07-46.04A65.114 65.114 0 0 1 65.11-.003h.002c36.09 0 65.346 29.256 65.346 65.346v317.713a65.292 65.292 0 0 1-19.125 46.171 65.292 65.292 0 0 1-46.171 19.125h-.001c-35.987 0-65.16-29.173-65.16-65.16L0 65.107ZM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781Z"/></svg>'
            )
        );

        /*
         * Campaign page.
         */
        $campaigns_page_hook_suffix = add_submenu_page(
            $parent_slug,
            /* translators: %s: Plugin name */
            sprintf(__('%s campaigns', 'kudos-donations'), 'Kudos'),
            __('Campaigns', 'kudos-donations'),
            'manage_options',
            'kudos-campaigns',
            function () {
                echo '<div id="kudos-settings"></div>';
            }
        );

        add_action("load-$campaigns_page_hook_suffix", function () {
            add_action("admin_enqueue_scripts", [$this, 'campaign_page_assets']);
        });

        /*
         * Settings page.
         */
        $settings_page_hook_suffix = add_submenu_page(
            $parent_slug,
            __('Kudos settings', 'kudos-donations'),
            __('Settings', 'kudos-donations'),
            'manage_options',
            'kudos-settings',
            function () {
                echo '<div id="kudos-settings"></div>';
            }
        );

        add_action("load-$settings_page_hook_suffix", function () {
            add_action('admin_enqueue_scripts', [$this, 'settings_page_assets']);
        });

        /*
         * Transaction page.
         */
        $transactions_page_hook_suffix = add_submenu_page(
            $parent_slug,
            /* translators: %s: Plugin name */
            sprintf(__('%s Transactions', 'kudos-donations'), 'Kudos'),
            __('Transactions', 'kudos-donations'),
            'manage_options',
            'kudos-transactions',
            function () {
                include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-transactions.php';
            }
        );

        add_action("load-$transactions_page_hook_suffix", function () {
            add_action("admin_enqueue_scripts", [$this, "transactions_page_assets"]);
            $this->table = new TransactionsTable($this->mapper);
            $this->table->prepare_items();
        });

        /*
         * Subscription page.
         */
        $subscriptions_page_hook_suffix = add_submenu_page(
            $parent_slug,
            /* translators: %s: Plugin name */
            sprintf(__('%s Subscriptions', 'kudos-donations'), 'Kudos'),
            __('Subscriptions', 'kudos-donations'),
            'manage_options',
            'kudos-subscriptions',
            function () {
                include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-subscriptions.php';
            }
        );

        add_action("load-$subscriptions_page_hook_suffix", function () {
            add_action("admin_enqueue_scripts", [$this, 'subscriptions_page_assets']);
            $this->table = new SubscriptionsTable($this->mapper, $this->payment);
            $this->table->prepare_items();
        });

        /*
         * Donor page.
         */
        $donors_page_hook_suffix = add_submenu_page(
            $parent_slug,
            /* translators: %s: Plugin name */
            sprintf(__('%s Donors', 'kudos-donations'), 'Kudos'),
            __('Donors', 'kudos-donations'),
            'manage_options',
            'kudos-donors',
            function () {
                include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-donors.php';
            }
        );

        add_action("load-$donors_page_hook_suffix", function () {
            add_action("admin_enqueue_scripts", [$this, 'donor_page_assets']);
            $this->table = new DonorsTable($this->mapper);
            $this->table->prepare_items();
        });

        /*
         * Debug page.
         */
        $debug_page_hook_suffix = add_submenu_page(
            $parent_slug,
            'Kudos Tools',
            'Tools',
            'manage_options',
            'kudos-tools',
            function () {
                require_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-tools.php';
            }
        );

        add_action(
            "admin_print_scripts-$debug_page_hook_suffix",
            function () {
                ?>
				<script>
                    document.addEventListener("DOMContentLoaded", function () {
                        let buttons = document.querySelectorAll('button[type="submit"].confirm')
                        for (let i = 0; i < buttons.length; i++) {
                            buttons[i].addEventListener('click', function (e) {
                                if (!confirm('<?php _e('Are you sure?', 'kudos-donations') ?>')) {
                                    e.preventDefault()
                                }
                            })
                        }
                    })
				</script>
                <?php
            }
        );
    }

    /**
     * Register assets for enqueuing in the block editor.
     */
    public function register_block_editor_assets()
    {
        wp_register_style(
            'kudos-donations-public',
            Assets::get_asset_url('/public/kudos-public.css'),
            ['kudos-donations-fonts'],
            $this->version
        );

        $editor_js = Assets::get_script('/blocks/kudos-button/index.js');
        wp_register_script(
            'kudos-donations-button',
            $editor_js['url'],
            $editor_js['dependencies'],
            $editor_js['version'],
            true
        );
    }

    /**
     * Assets specific to the settings page.
     */
    public function settings_page_assets()
    {
        // Enqueue the styles
        wp_enqueue_style(
            'kudos-donations-settings',
            Assets::get_asset_url('/admin/kudos-admin-settings.css'),
            [],
            $this->version
        );

        // Get and enqueue the script
        $admin_js = Assets::get_script('/admin/kudos-admin-settings.js');
        wp_enqueue_script(
            'kudos-donations-settings',
            $admin_js['url'],
            $admin_js['dependencies'],
            $admin_js['version'],
            true
        );

        wp_localize_script(
            'kudos-donations-settings',
            'kudos',
            [
                'version' => $this->version,
            ]
        );
        wp_set_script_translations('kudos-donations-settings', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages');

        do_action('kudos_admin_settings_page_assets', 'kudos-donations-settings');
    }

    /**
     * Assets specific to the Kudos Transactions page.
     */
    public function transactions_page_assets()
    {
        $transactions_js = Assets::get_script('/admin/kudos-admin-transactions.js');

        wp_enqueue_script(
            'kudos-donations-transactions',
            $transactions_js['url'],
            $transactions_js['dependencies'],
            $transactions_js['version'],
            false
        );

        // Load table assets.
        $table_handle = $this->table_page_assets();
        wp_localize_script(
            $table_handle,
            'kudos',
            [
                'confirmationDelete' => __('Are you sure you want to delete this transaction?', 'kudos-donations'),
            ]
        );
    }

    /**
     * Assets common to all CampaignTable pages.
     */
    private function table_page_assets(): string
    {
        $handle   = 'kudos-donations-table';
        $table_js = Assets::get_script('/admin/kudos-admin-table.js');

        wp_enqueue_script(
            $handle,
            $table_js['url'],
            $table_js['dependencies'],
            $table_js['version'],
            true
        );

        return $handle;
    }

    /**
     * Assets specific to the Kudos Subscriptions page.
     */
    public function subscriptions_page_assets()
    {
        // Load table assets.
        $table_handle = $this->table_page_assets();
        wp_localize_script(
            $table_handle,
            'kudos',
            [
                'confirmationCancel' => __('Are you sure you want to cancel this subscription?', 'kudos-donations'),
                'confirmationDelete' => __('Are you sure you want to delete this subscription?', 'kudos-donations'),
            ]
        );
    }

    /**
     * Assets specific to the Kudos Donors page.
     */
    public function donor_page_assets()
    {
        // Load table assets.
        $table_handle = $this->table_page_assets();
        wp_localize_script(
            $table_handle,
            'kudos',
            [
                'confirmationDelete' => __('Are you sure you want to delete this donor?', 'kudos-donations'),
            ]
        );
    }

    /**
     * Assets specific to the Kudos campaigns page.
     */
    public function campaign_page_assets()
    {
        // Enqueue the styles
        wp_enqueue_style(
            'kudos-donations-settings',
            Assets::get_asset_url('/admin/kudos-admin-settings.css'),
            [],
            $this->version
        );


        // Get and enqueue the script
        $admin_js = Assets::get_script('/admin/kudos-admin-campaigns.js');
        wp_enqueue_script(
            'kudos-donations-settings',
            $admin_js['url'],
            $admin_js['dependencies'],
            $admin_js['version'],
            true
        );

        wp_localize_script(
            'kudos-donations-settings',
            'kudos',
            [
                'version' => $this->version,
            ]
        );
        wp_set_script_translations('kudos-donations-settings', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages');

        do_action('kudos_admin_settings_page_assets', 'kudos-donations-settings');
    }

    /**
     * Actions triggered by request data in the admin.
     * Needs to be hooked to admin_init as it modifies headers.
     */
    public function admin_actions()
    {
        if (isset($_REQUEST['kudos_action'])) {
            $action = sanitize_text_field(wp_unslash($_REQUEST['kudos_action']));
            $nonce  = wp_unslash($_REQUEST['_wpnonce']);

            // Check nonce.
            if ( ! wp_verify_nonce($nonce, $action)) {
                die();
            }

            switch ($action) {
                case 'kudos_log_clear':
                    if ($this->logger->clear() === 0) {
                        new AdminNotice(__('Log cleared', 'kudos-donations'));
                    }
                    break;

                case 'kudos_clear_mollie':
                    Settings::remove_setting('vendor_mollie');
                    Settings::add_defaults();
                    break;

                case 'kudos_clear_all':
                    Settings::remove_settings();
                    Settings::add_defaults();
                    break;

                case 'kudos_clear_twig_cache':
                    if ($this->twig->clearCache()) {
                        new AdminNotice(__('Cache cleared', 'kudos-donations'));
                    }
                    break;

                case 'kudos_clear_object_cache':
                    if (wp_cache_flush()) {
                        new AdminNotice(__('Cache cleared', 'kudos-donations'));
                    }
                    break;

                case 'kudos_clear_transactions':
                    $records = $this->mapper
                        ->get_repository(TransactionEntity::class)
                        ->delete_all();
                    if ($records) {
                        new AdminNotice(
                            sprintf(
                            /* translators: %s: Number of records. */
                                _n('Deleted %s transaction', 'Deleted %s transactions', $records, 'kudos-donations'),
                                $records
                            )
                        );
                    }

                    break;

                case 'kudos_clear_donors':
                    $records = $this->mapper
                        ->get_repository(DonorEntity::class)
                        ->delete_all();
                    if ($records) {
                        new AdminNotice(
                            sprintf(
                            /* translators: %s: Number of records. */
                                _n('Deleted %s donor', 'Deleted %s donors', $records, 'kudos-donations'),
                                $records
                            )
                        );
                    }
                    break;

                case 'kudos_clear_subscriptions':
                    $records = $this->mapper
                        ->get_repository(SubscriptionEntity::class)
                        ->delete_all();
                    if ($records) {
                        new AdminNotice(
                            sprintf(
                            /* translators: %s: Number of records. */
                                _n(
                                    'Deleted %s subscription',
                                    'Deleted %s subscriptions',
                                    $records,
                                    'kudos-donations'
                                ),
                                $records
                            )
                        );
                    }
                    break;

                case 'kudos_recreate_database':
                    foreach (
                        [
                            SubscriptionEntity::get_table_name(),
                            TransactionEntity::get_table_name(),
                            DonorEntity::get_table_name(),
                        ] as $table
                    ) {
                        $this->mapper->delete_table($table);
                    }
                    $activator = $this->activator;
                    $activator->activate();
                    new AdminNotice(__('Database re-created', 'kudos-donations'));
                    break;

                case 'kudos_sync_mollie_transactions':
                    $mollie  = $this->mollie;
                    $updated = $mollie->sync_transactions();
                    if ($updated) {
                        new AdminNotice(
                            sprintf(
                            /* translators: %s: Number of records. */
                                _n(
                                    'Updated %s transaction',
                                    'Updated %s transactions',
                                    $updated,
                                    'kudos-donations'
                                ),
                                $updated
                            )
                        );
                        break;
                    }
                    new AdminNotice(__('No transactions need updating', 'kudos-donations'));
                    break;

                case 'kudos_add_missing_mollie_transactions':
                    $mollie  = $this->mollie;
                    $updated = $mollie->add_missing_transactions();
                    if ($updated) {
                        new AdminNotice(
                            sprintf(
                            /* translators: %s: Number of records. */
                                _n(
                                    'Added %s transaction',
                                    'Added %s transactions',
                                    $updated,
                                    'kudos-donations'
                                ),
                                $updated
                            )
                        );
                        break;
                    }
                    new AdminNotice(__('No transactions need adding', 'kudos-donations'));
                    break;
            }

            do_action('kudos_admin_actions_extra', $action);
        }
    }

    /**
     * Truncates the log file when over certain length.
     * Length defined by LoggerService::TRUNCATE_AT const.
     */
    public function truncate_log()
    {
        $this->logger->truncate();
    }

    /**
     * Register the kudos settings.
     */
    public function register_settings()
    {
        Settings::register_settings(self::get_settings());
    }

    /**
     * Returns all settings in array.
     *
     * @return array
     */
    public static function get_settings(): array
    {
        return
            [
                'show_intro'             => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'migration_actions'      => [
                    'type'    => 'array',
                    'default' => [],
                ],
                'vendor'                 => [
                    'type'         => 'string',
                    'show_in_rest' => true,
                    'default'      => 'mollie',
                ],
                'vendor_mollie'          => [
                    'type'              => 'object',
                    'default'           => [
                        'connected'       => false,
                        'recurring'       => false,
                        'mode'            => 'test',
                        'payment_methods' => [],
                        'test_key'        => '',
                        'live_key'        => '',
                    ],
                    'show_in_rest'      => [
                        'schema' => [
                            'type'       => 'object',
                            'properties' => [
                                'connected'       => [
                                    'type' => 'boolean',
                                ],
                                'recurring'       => [
                                    'type' => 'boolean',
                                ],
                                'mode'            => [
                                    'type' => 'string',
                                ],
                                'test_key'        => [
                                    'type' => 'string',
                                ],
                                'live_key'        => [
                                    'type' => 'string',
                                ],
                                'payment_methods' => [
                                    'type'  => 'array',
                                    'items' => [
                                        'type'       => 'object',
                                        'properties' => [
                                            'id'            => [
                                                'type' => 'string',
                                            ],
                                            'status'        => [
                                                'type' => 'string',
                                            ],
                                            'maximumAmount' => [
                                                'type'       => 'object',
                                                'properties' => [
                                                    'value'    => [
                                                        'type' => 'string',
                                                    ],
                                                    'currency' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'sanitize_callback' => [Settings::class, 'sanitize_vendor'],
                ],
                'email_receipt_enable'   => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'email_bcc'              => [
                    'type'              => 'string',
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'sanitize_email',
                ],
                'smtp_enable'            => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'smtp_host'              => [
                    'type'              => 'string',
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'smtp_encryption'        => [
                    'type'              => 'string',
                    'show_in_rest'      => true,
                    'default'           => 'tls',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'smtp_autotls'           => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'smtp_from'              => [
                    'type'              => 'string',
                    'show_in_rest'      => true,
                    'default'           => null,
                    'sanitize_callback' => 'sanitize_email',
                ],
                'smtp_username'          => [
                    'type'              => 'string',
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'smtp_password'          => [
                    'type'         => 'string',
                    'show_in_rest' => true,
                ],
                'smtp_port'              => [
                    'type'              => 'number',
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'intval',
                ],
                'spam_protection'        => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'return_message_enable'  => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'custom_return_enable'   => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'custom_return_url'      => [
                    'type'              => 'string',
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'esc_url_raw',
                ],
                'payment_vendor'         => [
                    'type'    => 'string',
                    'default' => 'mollie',
                ],
                'debug_mode'             => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'always_load_assets'     => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'donate_modal_in_footer' => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'disable_object_cache'   => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
            ];
    }
}
