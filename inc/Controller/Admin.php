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
use IseardMedia\Kudos\Infrastructure\Container\AbstractService;
use IseardMedia\Kudos\Migrations\Migrator;
use IseardMedia\Kudos\Service\ActivatorService;
use IseardMedia\Kudos\Service\AdminNotice;
use IseardMedia\Kudos\Service\MapperService;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Service\TwigService;
use IseardMedia\Kudos\Service\Vendor\MollieVendor;
use Psr\Log\LoggerInterface;

class Admin extends AbstractService
{
    /**
     * @var MapperService
     */
    private MapperService $mapper;
    /**
     * @var TransactionsTable
     */
    private TransactionsTable $table;
    /**
     * @var TwigService
     */
    private TwigService $twig;
    /**
     * @var PaymentService
     */
    private PaymentService $payment;
    /**
     * @var ActivatorService
     */
    private ActivatorService $activator;
    /**
     * @var MollieVendor
     */
    private MollieVendor $mollie;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Migrator
     */
    private Migrator $migrator;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct(
        MapperService $mapper,
        TwigService $twig,
        PaymentService $payment,
        ActivatorService $activator,
        MollieVendor $mollie_vendor,
        LoggerInterface $logger,
        Migrator $migrator
    ) {
        $this->mapper    = $mapper;
        $this->twig      = $twig;
        $this->payment   = $payment;
        $this->activator = $activator;
        $this->mollie    = $mollie_vendor;
        $this->logger    = $logger;
        $this->migrator  = $migrator;
    }

	public function register(): void {
		add_action('admin_init', [$this, 'admin_actions']);
		add_action('admin_init', [$this, 'check_migrations_pending']);
		add_action('admin_init', [$this, 'register_block_editor_assets']);
		add_action('kudos_remove_secret_action', [$this, 'remove_secret_action'], 10, 2);
		add_action('kudos_check_log', [$this, 'truncate_log']);
	}

    /**
     * Actions triggered by request data in the admin.
     * Needs to be hooked to admin_init as it modifies headers.
     */
    public function admin_actions(): void {
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

    public function check_migrations_pending(): void {
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
    public function register_block_editor_assets(): void {
        wp_register_style(
            'kudos-donations-public',
            Assets::get_style('admin/kudos-admin-campaigns.jsx.css'),
            [],
            KUDOS_VERSION
        );
    }

    /**
     * Create the Kudos Donations admin pages.
     */
    public function add_menu_pages(): void {
        $this->redirect_to_settings();
        $parent_slug = apply_filters('kudos_parent_settings_slug', 'kudos-campaigns');

        /*
         * TransactionPostType page.
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
         * DonorPostType page.
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

    public function redirect_to_settings(): void {
        $show_intro = Settings::get_setting('show_intro', true);
        if ($show_intro) {
            global $pagenow;
            if ($pagenow === 'admin.php' && $_GET['page'] === 'kudos-campaigns') {
                wp_redirect(admin_url('admin.php?page=kudos-settings'));
            }
        }
    }

    /**
     * Truncates the log file when over certain length.
     * Length defined by LoggerService::TRUNCATE_AT const.
     */
    public function truncate_log(): void {
	    $this->logger->truncate();
    }
}
