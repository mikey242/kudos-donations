<?php

namespace IseardMedia\Kudos\Controller;

use Exception;
use IseardMedia\Kudos\Controller\Table\DonorsTable;
use IseardMedia\Kudos\Controller\Table\SubscriptionsTable;
use IseardMedia\Kudos\Controller\Table\TransactionsTable;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Helpers\Assets;
use IseardMedia\Kudos\Helpers\Settings;
use IseardMedia\Kudos\Migrations\Migrator;
use IseardMedia\Kudos\Service\ActivatorService;
use IseardMedia\Kudos\Service\AdminNotice;
use IseardMedia\Kudos\Service\LoggerService;
use IseardMedia\Kudos\Service\MapperService;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Service\TwigService;
use IseardMedia\Kudos\Service\Vendor\MollieVendor;

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
     * @var \IseardMedia\Kudos\Service\Vendor\MollieVendor
     */
    private $mollie;
    /**
     * @var \IseardMedia\Kudos\Service\LoggerService
     */
    private $logger;
    /**
     * @var \IseardMedia\Kudos\Migrations\Migrator
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
        LoggerService $logger,
        Migrator $migrator
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
                'migrations_pending'     => [
                    'type'    => 'array',
                    'default' => [],
                ],
                'migration_history'      => [
                    'type' => 'array',
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
                'custom_smtp'            => [
                    'type'         => 'object',
                    'default'      => [
                        'from_email' => '',
                        'from_name'  => get_bloginfo('name'),
                        'host'       => '',
                        'port'       => '',
                        'encryption' => 'tls',
                        'autotls'    => false,
                        'username'   => '',
                        'password'   => '',
                    ],
                    'show_in_rest' => [
                        'schema' => [
                            'type'       => 'object',
                            'properties' => [
                                'from_email' => [
                                    'type' => 'string',
                                ],
                                'from_name'  => [
                                    'type' => 'string',
                                ],
                                'host'       => [
                                    'type' => 'string',
                                ],
                                'port'       => [
                                    'type' => 'number',
                                ],
                                'encryption' => [
                                    'type' => 'string',
                                ],
                                'autotls'    => [
                                    'type' => 'boolean',
                                ],
                                'username'   => [
                                    'type' => 'string',
                                ],
                                'password'   => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
                'smtp_enable'            => [
                    'type'              => 'boolean',
                    'show_in_rest'      => true,
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
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
                    break;

                case 'kudos_clear_all':
                    Settings::remove_settings();
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

                case 'kudos_migrate':
                    if (isset($_REQUEST['migration_version'])) {
                        $versions = wp_unslash($_REQUEST['migration_version']);
                    } else {
                        $versions = Settings::get_setting('migrations_pending');
                    }
                    if ($versions) {
                        foreach ($versions as $version) {
                            try {
                                $this->migrator->migrate($version);
                            } catch (Exception $e) {
                                new AdminNotice($e->getMessage(), 'warning');
                            }
                            if (($key = array_search($version, $versions)) !== false) {
                                unset($versions[$key]);
                            }
                            Settings::update_setting('migrations_pending', $versions);
                        }
                    }
            }
        }
    }

    public function check_migrations_pending()
    {
        $actions = Settings::get_setting('migrations_pending');
        if ($actions) {
            $form = "<form method='post'>";
            $form .= wp_nonce_field('kudos_migrate', '_wpnonce', true, false);
            $form .= "<button class='button-secondary confirm' name='kudos_action' type='submit' value='kudos_migrate'>";
            $form .= __("Update now", 'kudos-donations');
            $form .= "</button>";
            $form .= "</form>";
            new AdminNotice(
                __(
                    'Kudos Donations database needs updating before you can continue. Please make sure you backup your data before proceeding.',
                    'kudos-donations'
                ) . $form,
                'info',
                null,
                false
            );
        }
    }

    /**
     * Register assets for enqueuing in the block editor.
     */
    public function register_block_editor_assets()
    {
        wp_register_style(
            'kudos-donations-public',
            Assets::get_style('admin/kudos-admin-campaigns.jsx.css'),
            [],
            $this->version
        );
    }

    /**
     * Create the Kudos Donations admin pages.
     */
    public function add_menu_pages()
    {
        $this->redirect_to_settings();
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
            [$this, 'settings_page_markup']
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
            [$this, 'settings_page_markup']
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
                include_once KUDOS_PLUGIN_DIR . '/inc/View/kudos-admin-transactions.php';
            }
        );

        add_action("load-$transactions_page_hook_suffix", function () {
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
                include_once KUDOS_PLUGIN_DIR . '/inc/View/kudos-admin-subscriptions.php';
            }
        );

        add_action("load-$subscriptions_page_hook_suffix", function () {
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
                include_once KUDOS_PLUGIN_DIR . '/inc/View/kudos-admin-donors.php';
            }
        );

        add_action("load-$donors_page_hook_suffix", function () {
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
                require_once KUDOS_PLUGIN_DIR . '/inc/View/kudos-admin-tools.php';
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

    public function redirect_to_settings()
    {
        $show_intro = Settings::get_setting('show_intro', true);
        if ($show_intro) {
            global $pagenow;
            if ($pagenow === 'admin.php' && $_GET['page'] === 'kudos-campaigns') {
                wp_redirect(admin_url('admin.php?page=kudos-settings'));
            }
        }
    }

    public function settings_page_markup()
    {
        if ( ! Settings::get_setting('migrations_pending')) {
            echo '<div id="kudos-settings"></div>';
        }
    }

    /**
     * Assets specific to the settings page.
     */
    public function settings_page_assets()
    {
        // Enqueue the styles
        wp_enqueue_style(
            'kudos-donations-settings',
            Assets::get_style('admin/kudos-admin-settings.jsx.css'),
            [],
            $this->version
        );

        // Get and enqueue the script
        $admin_js = Assets::get_script('admin/kudos-admin-settings.jsx.js');
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
                'version'            => $this->version,
                'migrations_pending' => (bool)Settings::get_setting('migrations_pending'),
                'stylesheets'        => [Assets::get_style('admin/kudos-admin-settings.jsx.css')],
            ]
        );
        wp_set_script_translations('kudos-donations-settings', 'kudos-donations');

        do_action('kudos_admin_settings_page_assets', 'kudos-donations-settings');
    }

    /**
     * Assets specific to the Kudos campaigns page.
     */
    public function campaign_page_assets()
    {
        // Enqueue the styles
        wp_enqueue_style(
            'kudos-donations-settings',
            Assets::get_style('admin/kudos-admin-settings.jsx.css'),
            [],
            $this->version
        );

        // Get and enqueue the script
        $admin_js = Assets::get_script('admin/kudos-admin-campaigns.jsx.js');
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
                'version'     => $this->version,
                'stylesheets' => [Assets::get_style('admin/kudos-admin-settings.jsx.css') . "?ver=$this->version"],
            ]
        );
        wp_set_script_translations(
            'kudos-donations-settings',
            'kudos-donations',
            plugin_dir_path(__FILE__) . '/languages/'
        );

        do_action('kudos_admin_settings_page_assets', 'kudos-donations-settings');
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
     * Truncates the log file when over certain length.
     * Length defined by LoggerService::TRUNCATE_AT const.
     */
    public function truncate_log()
    {
        $this->logger->truncate();
    }
}
