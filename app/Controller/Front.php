<?php

namespace Kudos\Controller;

use Exception;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Assets;
use Kudos\Helpers\CustomPostType;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;

class Front
{
    /**
     * The version of this plugin.
     *
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * @var LoggerService
     */
    private $logger;

    /**
     * @var PaymentService
     */
    private $payment;

    /**
     * @var MapperService
     */
    private $mapper;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $version The version of this plugin.
     *
     */
    public function __construct(
        string $version,
        LoggerService $logger,
        PaymentService $payment,
        MapperService $mapper
    ) {
        $this->version = $version;
        $this->logger  = $logger;
        $this->payment = $payment;
        $this->mapper  = $mapper;
    }

    /**
     * Registers the button shortcode and block.
     */
    public function register_kudos()
    {
        $this->register_assets();
        $this->register_blocks();
        $this->register_post_types();
        $this->register_button_shortcode();
        if (Settings::get_setting('always_load_assets')) {
            $this->enqueue_assets();
        }
    }

    /**
     * Register the assets needed to display Kudos.
     * @return void
     */
    public function register_assets()
    {
        $public_js = Assets::get_script('/public/kudos-public.js');
        wp_register_script(
            'kudos-donations-public',
            $public_js['url'],
            $public_js['dependencies'],
            $public_js['version'],
            true
        );

        wp_localize_script(
            'kudos-donations-public',
            'kudos',
            [
                'stylesheets' => [Assets::get_style('/public/kudos-public.css')],
            ]
        );

        wp_set_script_translations('kudos-donations-public', 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages');

        wp_register_style(
            'kudos-donations-fonts',
            Assets::get_style('/public/kudos-fonts.css'),
            [],
            $this->version
        );
    }

    /**
     * Register the Kudos button block.
     */
    private function register_blocks()
    {
        register_block_type(
            KUDOS_PLUGIN_DIR . '/build/blocks/kudos-button/',
            [
                "render_callback" => [$this, "button_render_callback"],
            ]
        );
    }

    /**
     * Register the custom post types sued by Kudos.
     *
     * @return void
     */
    private function register_post_types()
    {
        new CustomPostType('kudos_campaign', [], [
            'goal'                  => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'show_goal'             => [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'additional_funds'      => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'initial_title'         => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'initial_description'   => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'address_enabled'       => [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'address_required'      => [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'message_enabled'       => [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'amount_type'           => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'fixed_amounts'         => [
                'type'              => 'string',
                'single'            => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'minimum_donation'      => [
                'type'              => 'integer',
                'sanitize_callback' => 'sanitize_float',
            ],
            'donation_type'         => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'theme_color'           => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'terms_link'            => [
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
            ],
            'privacy_link'          => [
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
            ],
            'show_return_message'   => [
                'type' => 'boolean',
            ],
            'use_custom_return_url' => [
                'type' => 'boolean',
            ],
            'custom_return_url'     => [
                'type' => 'string',
            ],
            'return_message_title'  => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'return_message_text'   => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ]);
    }

    /**
     * Register the kudos button shortcode.
     */
    private function register_button_shortcode()
    {
        // Register shortcode.
        add_shortcode(
            'kudos',
            function ($atts) {
                $atts = shortcode_atts(
                    [
                        'button_label' => __('Donate now', 'kudos-donations'),
                        'campaign_id'  => '',
                        'alignment'    => 'none',
                        'type'         => 'button',
                    ],
                    $atts,
                    'kudos'
                );

                return $this->button_render_callback($atts);
            }
        );
    }

    /**
     * Renders the kudos button and donation modals.
     *
     * @param array $atts Array of Kudos button/modal attributes.
     *
     * @return string|null
     */
    public function button_render_callback(array $atts): ?string
    {
        try {
            // Check if the current vendor is connected, otherwise throw an exception.
            if ( ! $this->payment::is_api_ready()) {
                /* translators: %s: Payment vendor (e.g. Mollie). */
                throw new Exception(
                    sprintf(
                        __("%s not connected.", 'kudos-donations'),
                        $this->payment::get_vendor_name()
                    )
                );
            }

            // Enqueue necessary resources.
            $this->enqueue_assets();

            // Create unique id for triggering.
            $id = Utils::generate_id('kudos-');

            if ($atts['type'] === 'button') {
                // Add modal to footer.
                add_action(
                    'wp_footer',
                    function () use ($atts, $id) {
                        echo $this->form_html($id, $atts);
                    }
                );

                // Output button.
                return $this->button_html($id, $atts);
            }

            return $this->form_html($id, $atts);
        } catch (Exception $e) {
            // Display error message if thrown and user is admin.
            if (current_user_can('manage_options')) {
                return '<p>' . $e->getMessage() . '</p>';
            }
        }

        // Nothing displayed to visitors if there is a problem.
        return null;
    }

    /**
     * Enqueue the styles and scripts.
     *
     * @return void
     */
    public function enqueue_assets()
    {
        wp_enqueue_script('kudos-donations-public');
        wp_enqueue_style('kudos-donations-fonts'); // Fonts need to be loaded in the main document.
    }

    public function form_html($id, $atts): string
    {
        return "<div id='form-$id' class='kudos-donations kudos-form' data-display-as='" . $atts['type'] . "' data-campaign='" . $atts['campaign_id'] . "' style='display: block'>
					</div>";
    }

    public function button_html($id, $atts): string
    {
        return "<div id='button-$id' class='kudos-button' data-label='" . $atts['button_label'] . "' data-target='form-$id' data-campaign='" . $atts['campaign_id'] . "' style='display: block'>
					</div>";
    }

    /**
     * Handles the various query variables and shows relevant modals.
     * @return void
     */
    public function handle_query_variables()
    {
        if (isset($_REQUEST['kudos_action']) && -1 !== $_REQUEST['kudos_action']) {
            $action = sanitize_text_field(wp_unslash($_REQUEST['kudos_action']));
            $nonce  = sanitize_text_field(wp_unslash($_REQUEST['kudos_nonce']));

            // Enqueue script / style in case we are on another page.
            $this->enqueue_assets();

            switch ($action) {
                case 'order_complete':
                    $order_id = sanitize_text_field($_REQUEST['kudos_order_id']);
                    // Return message modal.
                    if ( ! empty($order_id) && ! empty($nonce)) {
                        /** @var TransactionEntity $transaction */
                        $transaction = $this->mapper
                            ->get_repository(TransactionEntity::class)
                            ->get_one_by(['order_id' => $order_id]);
                        if ($transaction && wp_verify_nonce($nonce, $action . $order_id)) {
                            /** @var DonorEntity $donor */
                            $donor = $this->mapper
                                ->get_repository(DonorEntity::class)
                                ->get_one_by(['customer_id' => $transaction->customer_id]);

                            try {
                                $campaign = CustomPostType::get_post($transaction->campaign_id);
                            } catch (Exception $e) {
                                $logger = $this->logger;
                                $logger->warning('Error checking transaction: ' . $e->getMessage());

                                return;
                            }

                            $atts['theme_color'] = $campaign['theme_color'][0];

                            switch ($transaction->status) {
                                case 'paid':
                                    $vars                = [
                                        '{{value}}' => (! empty($transaction->currency) ? html_entity_decode(
                                                Utils::get_currency_symbol($transaction->currency)
                                            ) : '') . number_format_i18n(
                                                           $transaction->value,
                                                           2
                                                       ),
                                        '{{name}}'  => $donor->name,
                                        '{{email}}' => $donor->email,
                                    ];
                                    $atts['modal_title'] = strtr($campaign['return_message_title'][0], $vars);
                                    $atts['modal_text']  = strtr($campaign['return_message_text'][0], $vars);
                                    break;
                                case 'canceled':
                                    $atts['modal_title'] = __('Payment cancelled', 'kudos-donations');
                                    $atts['modal_text']  = __(
                                        'You have not been charged for this transaction.',
                                        'kudos-donations'
                                    );
                                    break;
                                default:
                                    $atts['modal_title'] = __('Thanks', 'kudos-donations');
                                    $atts['modal_text']  = __(
                                        'Your donation will be processed soon.',
                                        'kudos-donations'
                                    );
                                    break;
                            }

                            if ($atts) {
                                echo $this->message_modal_html(
                                    $atts['modal_title'],
                                    $atts['modal_text'],
                                    $atts['theme_color']
                                );
                            }
                        }
                    }
                    break;

                case 'cancel_subscription':
                    $subscription_id = sanitize_text_field($_REQUEST['kudos_subscription_id']);
                    // Cancel subscription modal.
                    if ( ! empty($nonce && ! empty($subscription_id))) {
                        /** @var SubscriptionEntity $subscription */
                        $subscription = $this->mapper
                            ->get_repository(SubscriptionEntity::class)
                            ->get_one_by(['subscription_id' => $subscription_id]);

                        // Bail if no subscription found.
                        if (null === $subscription) {
                            break;
                        }

                        if (wp_verify_nonce($nonce, $action)) {
                            if ($this->payment->cancel_subscription($subscription_id)) {
                                echo $this->message_modal_html(
                                    __('Subscription cancelled', 'kudos-donations'),
                                    __(
                                        'We will no longer be taking payments for this subscription. Thank you for your contributions.',
                                        'kudos-donations'
                                    )
                                );

                                break;
                            }
                        }

                        echo $this->message_modal_html(
                            __('Link expired', 'kudos-donations'),
                            __('Sorry, this link is no longer valid.', 'kudos-donations')
                        );
                    }
                    break;
            }
        }
    }

    /**
     * Create message modal with supplied header and body text.
     *
     * @param string $header The header text.
     * @param string $body The body text.
     *
     * @return string
     */
    private function message_modal_html(string $header, string $body, string $color = '#ff9f1c'): ?string
    {
        return "<div class='kudos-donations kudos-message' data-color='$color' data-title='$header' data-body='$body'></div>";
    }
}
