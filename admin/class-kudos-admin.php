<?php

namespace Kudos;

use Mollie\Api\Resources\Subscription;
use WP_REST_Server;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/admin
 * @author     Michael Iseard <michael@iseard.media>
 */
class Kudos_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Registers REST routes
	 *
	 * @since   2.0.0
	 * @return void
	 */
	public function register_routes() {

		// Mollie API key check
		$mollie = new Kudos_Mollie();
		$mollie->register_api_key_check();

		// Test Email
        $mailer = new Kudos_Mailer();
        $mailer->register_send_test_email();

        // Diagnostics
		register_rest_route('kudos/v1', 'diagnostics', [
			'methods'   => WP_REST_Server::READABLE,
			'callback'  => [$this, 'diagnostics'],
		]);
	}

	/**
     * Create the Kudos Donations admin pages
     *
	 * @since   2.0.0
	 */
	public function kudos_add_menu_pages() {

	    add_menu_page(
	        __('Kudos', 'kudos-donations'),
            __('Kudos', 'kudos-donations'),
            'manage_options',
            'kudos-settings',
            false,
		    'data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>
            ')

        );

		$settings_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			__( 'Kudos Settings', 'kudos-donations' ),
			__( 'Settings', 'kudos-donations' ),
			'manage_options',
			'kudos-settings',
			function() {
				echo '<div id="kudos-settings"></div>';
            }
		);
		add_action( "admin_print_scripts-{$settings_page_hook_suffix}", [$this, 'kudos_settings_page_assets'] );

		$transactions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Transactions', 'kudos-donations'), 'Kudos'),
			__('Transactions', 'kudos-donations'),
			'manage_options',
			'kudos-transactions',
			[$this, 'transactions_table']

		);
		add_action( "admin_print_scripts-{$transactions_page_hook_suffix}", [$this, 'kudos_transactions_page_assets'] );

		$subscriptions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Subscriptions', 'kudos-donations'), 'Kudos'),
			__('Subscriptions', 'kudos-donations'),
			'manage_options',
			'kudos-subscriptions',
			[$this, 'subscriptions_table']

		);
		add_action( "admin_print_scripts-{$subscriptions_page_hook_suffix}", [$this, 'kudos_subscriptions_page_assets'] );

		$donors_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Donors', 'kudos-donations'), 'Kudos'),
			__('Donors', 'kudos-donations'),
			'manage_options',
			'kudos-donors',
			[$this, 'donors_table']

		);
		add_action( "admin_print_scripts-{$donors_page_hook_suffix}", [$this, 'kudos_subscriptions_page_assets'] );

        // Add debug menu
        if(WP_DEBUG) {
	        add_submenu_page(
		        'kudos-settings',
		        'Kudos Debug',
		        'Debug',
		        'manage_options',
		        'kudos-debug',
		        [$this, 'kudos_debug']
	        );
        }

	}

	/**
     * Assets specific to the Kudos Settings page
     *
	 * @since   2.0.0
	 */
	public function kudos_settings_page_assets() {

	    $handle = $this->plugin_name . '-settings';

		wp_enqueue_script( $handle, get_asset_url('kudos-admin-settings.js'), [ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-data' ], $this->version, true );
		wp_localize_script($handle, 'kudos', [
			'version' => KUDOS_VERSION,
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'checkApiUrl' => rest_url('kudos/v1/mollie/admin'),
			'sendTestUrl' => rest_url('kudos/v1/email/test'),
			'getDiagnosticsUrl' => rest_url('kudos/v1/diagnostics'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		]);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_DIR . 'languages');
		wp_enqueue_style( 'kudos-donations-admin-react', get_asset_url('kudos-admin-settings.css'), [ 'wp-components' ], $this->version,'all' );
	}

	/**
	 * Assets specific to the Kudos Transactions page
	 *
	 * @since   2.0.0
	 */
	public function kudos_transactions_page_assets() {
		wp_enqueue_script( $this->plugin_name . '-transactions', get_asset_url('kudos-admin-transactions.js'), [ 'jquery' ], $this->version, false );
		wp_localize_script($this->plugin_name . '-transactions', 'kudos', [
			'confirmation' => __('Are you sure you want to delete this transaction?', 'kudos-donations'),
		]);
	}

	/**
	 * Assets specific to the Kudos Subscriptions page
	 *
	 * @since   2.0.0
	 */
	public function kudos_subscriptions_page_assets() {
		wp_enqueue_script( $this->plugin_name . '-subscriptions', get_asset_url('kudos-admin-subscriptions.js'), [ 'jquery' ], $this->version, false );
		wp_localize_script($this->plugin_name . '-subscriptions', 'kudos', [
			'confirmation' => __('Are you sure you want to cancel this subscription?', 'kudos-donations'),
		]);
	}

	/**
	 * Creates the transactions table
     *
	 * @since    1.0.0
	 */
	public function transactions_table() {
	    $table = new Transactions_Table();
	    $table->prepare_items();
		$message = '';

		if ('delete' === $table->current_action()) {
			$message = __('Transaction deleted', 'kudos-donations');
		} elseif ('bulk-delete' === $table->current_action() && isset($_REQUEST['bulk-action'])) {
			/* translators: %: Number of transactions */
			$message = sprintf(__('% transaction(s) deleted', 'kudos-donations'), count($_REQUEST['bulk-action']));
        }
	    ?>
	    <div class="wrap">
		    <h1 class="wp-heading-inline"><?php _e('Transactions', 'kudos-donations'); ?></h1>
		    <?php if (!empty($_REQUEST['s'])) { ?>
            <span class="subtitle">
                <?php
                    /* translators: %s: Search term */
                    printf(__('Search results for “%s”'), $_REQUEST['s'])
                ?>
            </span>
            <?php } ?>
            <p><?php _e("Your recent Kudos transactions",'kudos-donations');?></p>
            <?php if($message) { ?>
                <div class="updated below-h2" id="message"><p><?php echo esc_html($message); ?></p></div>
            <?php } ?>
            <form id="transactions-table" method="POST">
            <?php
                $table->display();
            ?>
            </form>
	    </div>
	    <?php
    }

	/**
	 * Creates the subscriptions table
	 *
	 * @since    1.1.0
	 */
	public function subscriptions_table() {
		$table = new Subscriptions_Table();
		$table->prepare_items();
		$message = '';

		if ('cancel' === $table->current_action()) {
			$message = __('Subscription cancelled', 'kudos-donations');
		} elseif ('bulk-cancel' === $table->current_action() && isset($_REQUEST['bulk-action'])) {
			/* translators: %s: Number of transactions */
			$message = sprintf(__('%s subscription(s) cancelled', 'kudos-donations'), count($_REQUEST['bulk-action']));
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Subscriptions', 'kudos-donations'); ?></h1>
			<?php if (!empty($_REQUEST['s'])) { ?>
                <span class="subtitle">
                <?php
                /* translators: %s: Search term */
                printf(__('Search results for “%s”'), $_REQUEST['s'])
                ?>
            </span>
			<?php } ?>
            <p><?php _e("Your recent Kudos subscriptions",'kudos-donations');?></p>
			<?php if($message) { ?>
                <div class="updated below-h2" id="message"><p><?php echo esc_html($message); ?></p></div>
			<?php } ?>
            <form id="subscriptions-table" method="POST">
				<?php
				$table->display();
				?>
            </form>
        </div>
		<?php
	}

	/**
	 * Creates the donors table
	 *
	 * @since    1.1.0
	 */
	public function donors_table() {
		$table = new Donors_Table();
		$table->prepare_items();
		$message = '';

		if ('cancel' === $table->current_action()) {
			$message = __('Subscription cancelled', 'kudos-donations');
		} elseif ('bulk-cancel' === $table->current_action() && isset($_REQUEST['bulk-action'])) {
			/* translators: %s: Number of transactions */
			$message = sprintf(__('%s donors(s) deleted', 'kudos-donations'), count($_REQUEST['bulk-action']));
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Donors', 'kudos-donations'); ?></h1>
			<?php if (!empty($_REQUEST['s'])) { ?>
                <span class="subtitle">
                <?php
                /* translators: %s: Search term */
                printf(__('Search results for “%s”'), $_REQUEST['s'])
                ?>
            </span>
			<?php } ?>
            <p><?php _e("Your recent Kudos donors",'kudos-donations');?></p>
			<?php if($message) { ?>
                <div class="updated below-h2" id="message"><p><?php echo esc_html($message); ?></p></div>
			<?php } ?>
            <form id="subscriptions-table" method="POST">
				<?php
				$table->display();
				?>
            </form>
        </div>
		<?php
	}

	/**
	 * Exports table data if request present.
     * Needs to be hooked to admin_init as it modifies headers.
     *
	 * @since    1.0.1
	 */
	public function export_csv() {

	    if(isset($_REQUEST['export_transactions'])) {

	        $table = new Transactions_Table();
	        $table->export();

	    }

		if(isset($_REQUEST['export_subscriptions'])) {

			$table = new Subscriptions_Table();
			$table->export();

		}

		if(isset($_REQUEST['export_donors'])) {

			$table = new Donors_Table();
			$table->export();

		}
	}

	/**
	 * Returns diagnostic information
	 *
	 * @since   2.0.0
	 * @return string
	 */
	public function diagnostics() {

	    $response = [
	        'phpVersion' => phpversion(),
            'mbstring' => extension_loaded('mbstring'),
            'invoiceWriteable'  => Kudos_Invoice::isWriteable(),
            'logWriteable'  => Kudos_Logger::isWriteable(),
            'permalinkStructure' => get_option( 'permalink_structure' )
        ];

		wp_send_json_success($response);
		wp_die();
	}

	/**
	 * Debug page render
     *
     * @since   2.0.0
	 */
	public function kudos_debug() {

	    $kudos_donor = new Kudos_Donor();
	    $kudos_mollie = new Kudos_Mollie();

		//Get the active tab from the $_GET param
		$default_tab = null;
		$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
		?>

		<div class="wrap">

            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=kudos-debug" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Log</a>
                <a href="?page=kudos-debug&tab=subscriptions" class="nav-tab <?php if($tab==='subscriptions'):?>nav-tab-active<?php endif; ?>">Subscriptions</a>
            </nav>

            <div class="tab-content">

	        <?php

            switch($tab):
                case 'subscriptions':
                    $donors = $kudos_donor->get_all();
                    if($donors) {
                        foreach($donors as $donor) {

                            $subscriptions = $kudos_mollie->get_subscriptions($donor->customer_id);

                            if(!count($subscriptions)) {
                                continue;
                            }

                            echo "<h3><strong>" . $donor->email . "</strong> <span>(" . $donor->customer_id . ")</span></h3>";
                            echo "<form action=". admin_url( 'admin-post.php' ) ." method='post'>";
                                wp_nonce_field('cancel_subscription', '_wpnonce');
                                echo "<input type='hidden' name='action' value='cancel_subscription'>";
                                echo "<input type='hidden' name='customerId' value='". $donor->customer_id ."'>";

                            /** @var Subscription $subscription */
                            foreach ($subscriptions as $subscription) {
                                    echo "<table class='widefat'>";
                                        echo "<tbody>";

                                            echo "<tr>";
                                                echo "<td class='row-title'>id</td>";
                                                echo "<td>" . $subscription->id . "</td> ";
                                            echo "</tr>";

                                            echo "<tr class='alternate'>";
                                                echo "<td class='row-title'>status</td>";
                                                echo "<td>$subscription->status" . ($subscription->status !== 'canceled' ? " <button name='subscriptionId' type='submit' value='$subscription->id'>Cancel</button>" : "") . "</td>";
                                            echo "</tr>";

                                            echo "<tr>";
                                                echo "<td class='row-title'>amount</td>";
                                                echo "<td>" . $subscription->amount->value . "</td>";
                                            echo "</tr> ";

                                            echo "<tr class='alternate'>";
                                                echo "<td class='row-title'>interval</td>";
                                                echo "<td>" . $subscription->interval . "</td>";
                                            echo "</tr> ";

                                            echo "<tr>";
                                                echo "<td class='row-title'>times</td>" ;
                                                echo "<td>". $subscription->times . "</td>";
                                            echo "</tr>";

                                            echo "<tr class='alternate'>";
                                                echo "<td class='row-title'>next payment</td>";
                                                echo "<td>". ($subscription->nextPaymentDate ?? 'n/a') . "</td>";
                                            echo "</tr>";

                                            echo "<tr>";
                                                echo "<td class='row-title'>webhookUrl</td>" ;
                                                echo "<td>". $subscription->webhookUrl . "</td>";
                                            echo "</tr>";

                                        echo "</tbody>";
                                    echo "</table>";
                                    echo "<br class='clear'>";
                                }
                            echo "</form>";
                        }
                    }
                    break;
	            default:
                    $kudos_logger = new Kudos_Logger();
                    $logArray = $kudos_logger->getAsArray();
                    ?>
                    <table class='form-table'><tbody>
                        <tr>
                            <th class='row-title'>Date</th>
                            <th>Level</th>
                            <th>Message</th>
                        </tr>
                    <?php foreach ($logArray as $key=>$log) {

                            $level = $log['type'];
	                        $style = 'border-left-width: 4px; border-left-style: solid;';

                            switch ($level) {
                                case 'CRITICAL':
                                case 'ERROR':
                                    $class = 'notice-error';
                                    break;
                                case 'DEBUG':
                                    $class='';
                                    $style='';
                                    break;
                                default:
	                                $class = 'notice-' . strtolower( $level );
                            }

                        echo "<tr style='$style' class='". ($key %2===0 ? 'alternate' : null) ." $class'>";

                            echo "<td>";
                                echo($log['date']);
                            echo "</td>";
                            echo "<td>";
                                echo($log['type']);
                            echo "</td>";
                            echo "<td>";
                                echo($log['message']);
                            echo "</td>";

                        echo "</tr>";
                    }
                    echo "</tbody></table>";

            endswitch;

	    ?>
        </div>

		</div>
        <?php
	}

	public function cancel_subscription() {
	    if(!wp_verify_nonce($_REQUEST['_wpnonce'], 'cancel_subscription')) {
	        echo "Nope!";
	        die;
	    }
	    $kudos_mollie = new Kudos_Mollie();
	    $subscription = $kudos_mollie->cancel_subscription($_REQUEST['subscriptionId'], $_REQUEST['customerId']);
	    if($subscription) {
	        echo "Subscription canceled";
	    }
	}

	/**
     * Register the plugin settings
     *
	 * @since    1.0.1
	 */
	public function register_settings() {

		require_once KUDOS_DIR . 'admin/kudos-settings.php';

	}

}
