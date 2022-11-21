<?php

namespace IseardMedia\Kudos\Service\Vendor;

use Exception;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Resources\SubscriptionCollection;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Helpers\CustomPostType;
use IseardMedia\Kudos\Helpers\Settings;
use IseardMedia\Kudos\Helpers\Utils;
use IseardMedia\Kudos\Service\LoggerService;
use IseardMedia\Kudos\Service\MapperService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class MollieVendor implements VendorInterface
{
    /**
     * This is the name of the vendor as displayed to the user.
     */
    public const VENDOR_NAME = 'Mollie';

    /**
     * Instance of MollieApiClient.
     *
     * @var MollieApiClient
     */
    private $api_client;
    /**
     * The API mode (test or live).
     *
     * @var string
     */
    private $api_mode;
    /**
     * @var \IseardMedia\Kudos\Service\LoggerService
     */
    private $logger;
    /**
     * @var \IseardMedia\Kudos\Service\MapperService
     */
    private $mapper;
    /**
     * @var array
     */
    private $api_keys;

    /**
     * Mollie constructor.
     */
    public function __construct(MapperService $mapper_service, LoggerService $logger_service)
    {
        $this->logger = $logger_service;
        $this->mapper = $mapper_service;

        $settings = Settings::get_setting('vendor_mollie');

        if ( ! $settings === false) {
            $this->api_client = new MollieApiClient();
            $this->api_keys   = [
                'test' => $settings['test_key'] ?? '',
                'live' => $settings['live_key'] ?? '',
            ];

            $this->set_api_mode($settings['mode'] ?? 'test');
            $this->set_user_agent();
        }
    }

    /**
     * Change the API client to the key for the specified mode.
     */
    private function set_api_mode(?string $mode)
    {
        $this->api_mode = $mode;
        // Gets the key associated with the specified mode.
        $key = $this->api_keys[$mode] ?? false;

        if ($key) {
            try {
                $this->api_client->setApiKey($key);
            } catch (ApiException $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * Sets the user agent for identifying requests made with this plugin.
     */
    private function set_user_agent()
    {
        global $wp_version;
        $this->api_client->addVersionString("KudosDonations/" . KUDOS_VERSION);
        $this->api_client->addVersionString("WordPress/" . $wp_version);
    }

    public static function supports_recurring(): bool
    {
        return true;
    }

    /**
     * Returns a translated string of the sequence type.
     *
     * @param string $text Mollie sequence type code.
     *
     * @return string
     */
    public static function get_sequence_type(string $text): string
    {
        switch ($text) {
            case 'oneoff':
                return __('One-off', 'kudos-donations');
            case 'first':
                return __('Recurring (first payment)', 'kudos-donations');
            default:
                return __('Recurring', 'kudos-donations');
        }
    }

    /**
     * Check the Mollie api keys for both test and live keys. Sends a JSON response.
     */
    public function check_api_keys(WP_REST_Request $request)
    {
        $keys = $request->get_param('keys');

        if ($keys) {
            $this->api_keys = [
                'test' => $keys['test_key'] ?? '',
                'live' => $keys['live_key'] ?? '',
            ];
        }

        Settings::update_array(
            'vendor_mollie',
            [
                'connected' => false,
                'recurring' => false,
            ]
        );

        $mode = $this->get_api_mode();

        // Check if both fields are empty.
        if (empty($this->api_keys[$mode])) {
            wp_send_json_error(
                [
                    /* translators: %s: API mode */
                    'message' => sprintf(
                        __('Please enter your %s API key.', 'kudos-donations'),
                        $mode
                    ),
                    'setting' => Settings::get_setting('vendor_mollie'),
                ]
            );
        }

        foreach ($this->api_keys as $type => $apiKey) {
            if ($apiKey) {
                // Check that the api key corresponds to each mode.
                if (substr($apiKey, 0, 5) !== $type . "_") {
                    wp_send_json_error(
                        [
                            /* translators: %s: API mode */
                            'message' => sprintf(
                                __('%1$s API key should begin with %2$s', 'kudos-donations'),
                                ucfirst($type),
                                $type . '_'
                            ),
                            'setting' => Settings::get_setting('vendor_mollie'),
                        ]
                    );
                }

                // Test the api key.
                if ( ! $this->refresh_api_connection($apiKey)) {
                    wp_send_json_error(
                        [
                            /* translators: %s: API mode */
                            'message' => sprintf(
                                __(
                                    'Error connecting with Mollie, please check the %s API key and try again.',
                                    'kudos-donations'
                                ),
                                ucfirst($type)
                            ),
                            'setting' => Settings::get_setting('vendor_mollie'),
                        ]
                    );
                }
            }
        }

        // Update vendor settings.
        Settings::update_array(
            'vendor_mollie',
            [
                'test_key'        => $this->api_keys['test'],
                'live_key'        => $this->api_keys['live'],
                'recurring'       => $this->can_use_recurring(),
                'mode'            => $this->get_api_mode(),
                'connected'       => true,
                'payment_methods' => array_map(function ($method) {
                    return [
                        'id'            => $method->id,
                        'status'        => $method->status,
                        'maximumAmount' => (array)$method->maximumAmount,
                    ];
                },
                    (array)$this->get_payment_methods()),
            ]
        );

        wp_send_json_success(
            [
                'message' =>
                /* translators: %s: API mode */
                    __('API connection was successful!', 'kudos-donations'),
                'setting' => Settings::get_setting('vendor_mollie'),
            ]
        );
    }

    /**
     * Returns the api mode.
     *
     * @return string
     */
    public function get_api_mode(): string
    {
        return $this->api_mode;
    }

    /**
     * Checks the provided api key by attempting to get associated payments.
     *
     * @param string $api_key API key to test.
     *
     * @return bool
     */
    public function refresh_api_connection(string $api_key): bool
    {
        if ( ! $api_key) {
            return false;
        }

        try {
            // Perform test call to verify api key.
            $mollie_api = $this->api_client;
            $mollie_api->setApiKey($api_key);
            $mollie_api->payments->page();

            return true;
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * Uses get_payment_methods to determine if account can receive recurring payments.
     *
     * @return bool
     */
    public function can_use_recurring(): bool
    {
        $methods = $this->get_payment_methods([
            'sequenceType' => 'recurring',
        ]);

        if ($methods) {
            return $methods->count > 0;
        }

        return false;
    }

    /**
     * Gets a list of payment methods for the current Mollie account
     *
     * @param array $options https://docs.mollie.com/reference/v2/methods-api/list-methods
     *
     * @return BaseCollection|MethodCollection|null
     */
    public function get_payment_methods(array $options = [])
    {
        try {
            return $this->api_client->methods->allActive($options);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return null;
        }
    }

    /**
     * Returns all subscriptions for customer.
     *
     * @param string $customer_id Mollie customer id.
     *
     * @return SubscriptionCollection|false
     */
    public function get_subscriptions(string $customer_id)
    {
        $mollie_api = $this->api_client;

        try {
            $customer = $mollie_api->customers->get($customer_id);

            return $customer->subscriptions();
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * Cancel the specified subscription.
     *
     * @param SubscriptionEntity $subscription Instance of SubscriptionEntity.
     *
     * @return bool
     */
    public function cancel_subscription(SubscriptionEntity $subscription): bool
    {
        $customer = $this->get_customer($subscription->customer_id);

        // Bail if no subscription found locally or if not active.
        if ('active' !== $subscription->status || null === $customer) {
            return false;
        }

        // Cancel the subscription via Mollie's API.
        try {
            $response = $customer->cancelSubscription($subscription->subscription_id);

            /** @var Subscription $response */
            return ($response->status === 'canceled');
        } catch (ApiException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Get the customer from Mollie.
     *
     * @param $customer_id
     *
     * @return Customer|null
     */
    public function get_customer($customer_id): ?Customer
    {
        try {
            return $this->api_client->customers->get($customer_id);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return null;
        }
    }

    /**
     * Gets specified payment.
     *
     * @param string $vendor_payment_id Mollie payment id.
     *
     * @return bool|Payment
     */
    public function get_payment(string $vendor_payment_id)
    {
        try {
            return $this->api_client->payments->get($vendor_payment_id);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }

    /**
     * Create a Mollie customer.
     *
     * @param string $email Donor email address.
     * @param string $name Donor name.
     *
     * @return bool|Customer
     */
    public function create_customer(string $email, string $name)
    {
        $customer_array = [
            'email' => $email,
        ];

        if ($name) {
            $customer_array['name'] = $name;
        }

        try {
            return $this->api_client->customers->create($customer_array);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * Creates a payment and returns it as an object.
     *
     * @param array $payment_args Parameters to pass to mollie to create a payment.
     * @param $order_id
     * @param string|null $customer_id
     *
     * @return string
     */
    public function create_payment(array $payment_args, $order_id, ?string $customer_id): string
    {
//        wp_send_json_error($payment_args);
        // Set payment frequency.
        $payment_args['payment_frequency'] = $payment_args['recurring'] === "true" ? $payment_args['recurring_frequency'] : 'oneoff';
        $payment_args['value']             = number_format($payment_args['value'], 2, '.', '');
        $frequency_text                    = self::get_frequency_name($payment_args['payment_frequency']);
        $sequence_type                     = "true" === $payment_args['recurring'] ? 'first' : 'oneoff';
        $redirect_url                      = $payment_args['return_url'];

        // Add order id query arg to return url if option to show message enabled.
        try {
            $campaign = CustomPostType::get_post($payment_args['campaign_id']);
            if ( ! empty($campaign['show_return_message'])) {
                $action       = 'order_complete';
                $redirect_url = add_query_arg(
                    [
                        'kudos_action'   => 'order_complete',
                        'kudos_order_id' => $order_id,
                        'kudos_nonce'    => wp_create_nonce($action . $order_id),
                    ],
                    $payment_args['return_url']
                );
            }
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
        }

        // Create payment settings.
        $payment_array = [
            "amount"       => [
                'currency' => $payment_args['currency'],
                'value'    => $payment_args['value'],
            ],
            'redirectUrl'  => $redirect_url,
            'webhookUrl'   => $this->get_webhook_url(),
            'sequenceType' => $sequence_type,
            'description'  => sprintf(
            /* translators: %s: The order id */
                __('Kudos Donation (%1$s) - %2$s', 'kudos-donations'),
                $frequency_text,
                $order_id
            ),
            'metadata'     => [
                'order_id'    => $order_id,
                'interval'    => $payment_args['payment_frequency'],
                'years'       => $payment_args['recurring_length'],
                'email'       => $payment_args['email'],
                'name'        => $payment_args['name'],
                'campaign_id' => $payment_args['campaign_id'],
            ],
        ];

        // Link payment to customer if specified.
        if ($customer_id) {
            $payment_array['customerId'] = $customer_id;
        }

        try {
            $payment = $this->api_client->payments->create($payment_array);

            $this->logger->info(
                "New $this payment created.",
                ['oder_id' => $order_id, 'sequence_type' => $payment->sequenceType]
            );

            return $payment->getCheckoutUrl();
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * Returns subscription frequency name based on number of months.
     *
     * @param string $frequency Mollie frequency code.
     *
     * @return string
     */
    public static function get_frequency_name(string $frequency): string
    {
        switch ($frequency) {
            case '12 months':
                return __('Yearly', 'kudos-donations');
            case '1 month':
                return __('Monthly', 'kudos-donations');
            case '3 months':
                return __('Quarterly', 'kudos-donations');
            case 'oneoff':
                return __('One-off', 'kudos-donations');
            default:
                return $frequency;
        }
    }

    /**
     * Returns the Mollie Rest URL.
     *
     * @return string
     */
    public static function get_webhook_url(): string
    {
        $route = "kudos/v1/payment/webhook";

        // Use APP_URL if defined in .env file.
        if (isset($_ENV['APP_URL'])) {
            return $_ENV['APP_URL'] . 'wp-json/' . $route;
        }

        // Otherwise, return normal rest URL.
        return rest_url($route);
    }

    /**
     * Mollie webhook handler.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function rest_webhook(WP_REST_Request $request)
    {
        // Sanitize request params.
        $request->sanitize_params();

        // ID is case-sensitive (e.g: tr_HUW39xpdFN).
        $payment_id = $request->get_param('id');

        // Create action with payment_id as parameter.
        do_action('kudos_mollie_webhook_requested', $payment_id);

        // Mollie API.
        $mollie = $this->api_client;

        // Log request.
        $this->logger->info(
            "Webhook requested by $this.",
            [
                'payment_id' => $payment_id,
            ]
        );

        try {
            /**
             * Create success response object.
             *
             * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
             */
            $response = rest_ensure_response(
                [
                    'success' => true,
                    'id'      => $payment_id,
                ]
            );

            $response->add_link('self', rest_url($request->get_route()));

            /**
             * Get the payment object from Mollie.
             *
             * @link https://docs.mollie.com/reference/v2/payments-api/get-payment
             */
            $payment = $mollie->payments->get($payment_id);

            // Log payment retrieval.
            $this->logger->debug(
                "Payment retrieved from Mollie.",
                [
                    'transaction_id' => $payment_id,
                    'status'         => $payment->status,
                    'sequence_type'  => $payment->sequenceType,
                    'has_refunds'    => $payment->hasRefunds(),
                ]
            );

            /**
             * Get transaction from database.
             * e.g. One-off, First, Recurring (only with refunds).
             *
             * @var TransactionEntity $transaction
             */
            $transaction = $this->mapper
                ->get_repository(TransactionEntity::class)
                ->get_one_by(
                    [
                        'order_id'       => $payment->metadata->order_id ?? '',
                        // Recurring payment objects do not have metadata.
                        'transaction_id' => $payment_id,
                    ],
                    'OR'
                );

            /**
             * Create new transaction if this is a recurring payment and none found.
             * e.g. New recurring payment.
             */
            if ( ! $transaction && $payment->hasSequenceTypeRecurring()) {
                $this->logger->debug('Recurring payment received, creating transaction.', [
                    'subscription_id' => $payment->subscriptionId,
                ]);
                $customer     = $mollie->customers->get($payment->customerId);
                $subscription = $customer->getSubscription($payment->subscriptionId);
                $transaction  = new TransactionEntity([
                    'order_id'    => Utils::generate_id('kdo_'),
                    'campaign_id' => $subscription->metadata->campaign_id ?? '',
                ]);
            }

            /**
             * We should have a transaction by now.
             * To not leak any information to malicious third parties, it is recommended
             * Always return a 200 OK response even if the ID is not known to your system.
             *
             * @link https://docs.mollie.com/overview/webhooks#how-to-handle-unknown-ids
             */
            if ( ! $transaction) {
                $this->logger->warning(
                    'Webhook received for unknown transaction. Aborting',
                    ['transaction_id' => $payment_id]
                );

                return $response;
            }

            // Update transaction status.
            $transaction->set_fields(
                [
                    'status' => $payment->status,
                    $payment->status,
                ]
            );

            // Create action with order_id as parameter.
            do_action("kudos_transaction_$payment->status", $transaction->order_id);

            if ($payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks()) {
                /*
                 * The payment is paid and isn't refunded or charged back.
                 * Time to check if this is a duplicate before processing.
                 */
                if ($payment_id === $transaction->transaction_id) {
                    $this->logger->debug('Duplicate webhook detected. Ignoring', ['transaction_id' => $payment_id]);

                    return $response;
                }

                // Update transaction.
                $transaction->set_fields(
                    [
                        'status'          => $payment->status,
                        'transaction_id'  => $payment->id,
                        'customer_id'     => $payment->customerId,
                        'value'           => $payment->amount->value,
                        'currency'        => $payment->amount->currency,
                        'sequence_type'   => $payment->sequenceType,
                        'method'          => $payment->method,
                        'mode'            => $payment->mode,
                        'subscription_id' => $payment->subscriptionId,
                    ]
                );

                // Set up recurring payment if sequence is first.
                if ($payment->hasSequenceTypeFirst()) {
                    $this->logger->info('Creating subscription.', $transaction->to_array());
                    $this->create_subscription(
                        $transaction,
                        $payment->mandateId,
                        $payment->metadata->interval,
                        $payment->metadata->years
                    );
                }
            } elseif ($payment->hasRefunds()) {
                /*
                 * The payment has been (partially) refunded.
                 * The status of the payment is still "paid".
                 */
                do_action('kudos_mollie_refund', $transaction->order_id);

                $transaction->set_fields(
                    [
                        'refunds' => json_encode(
                            [
                                'refunded'  => $payment->getAmountRefunded(),
                                'remaining' => $payment->getAmountRemaining(),
                            ]
                        ),
                    ]
                );

                $this->logger->info('Payment refunded.', ['transaction' => $transaction]);
            }
        } catch (ApiException $e) {
            $this->logger->error("$this webhook exception: " . $e->getMessage(), ['payment_id' => $payment_id]);

            // Send fail response to Mollie so that they know to try again.
            return rest_ensure_response(
                new WP_REST_Response([
                    'success' => false,
                    'id'      => $payment_id,
                ], 500)
            );
        }

        /**
         * Save transaction to database and
         * return response to Mollie.
         */
        $this->mapper->save($transaction);

        return $response;
    }

    /**
     * Creates a subscription based on the provided TransactionEntity
     *
     * @param TransactionEntity $transaction
     * @param string $mandate_id
     * @param string $interval
     * @param string $years
     *
     * @return false|Subscription
     */
    public function create_subscription(
        TransactionEntity $transaction,
        string $mandate_id,
        string $interval,
        string $years
    ) {
        $customer_id = $transaction->customer_id;
        $start_date  = gmdate('Y-m-d', strtotime('+' . $interval));
        $currency    = 'EUR';
        $value       = number_format($transaction->value, 2);

        $subscription_array = [
            'amount'      => [
                'value'    => $value,
                'currency' => $currency,
            ],
            'webhookUrl'  => $this->get_webhook_url(),
            'mandateId'   => $mandate_id,
            'interval'    => $interval,
            'startDate'   => $start_date,
            'description' => sprintf(
            /* translators: %1$s: Subscription interval. %2$s: Order id. */
                __('Kudos Subscription (%1$s) - %2$s', 'kudos-donations'),
                $interval,
                $transaction->order_id
            ),
            'metadata'    => [
                'campaign_id' => $transaction->campaign_id,
            ],
        ];

        if ('test' === $transaction->mode) {
            unset($subscription_array['startDate']);  // Disable for test mode.
        }

        if ($years && $years > 0) {
            $subscription_array['times'] = Utils::get_times_from_years($years, $interval);
        }

        $customer      = $this->get_customer($customer_id);
        $valid_mandate = $this->check_mandate($customer, $mandate_id);

        // Create subscription if valid mandate found
        if ($valid_mandate) {
            try {
                $subscription       = $customer->createSubscription($subscription_array);
                $kudos_subscription = new SubscriptionEntity(
                    [
                        'transaction_id'  => $transaction->transaction_id,
                        'customer_id'     => $customer_id,
                        'frequency'       => $interval,
                        'years'           => $years,
                        'value'           => $value,
                        'currency'        => $currency,
                        'subscription_id' => $subscription->id,
                        'status'          => $subscription->status,
                    ]
                );
                $this->mapper->save($kudos_subscription);

                return $subscription;
            } catch (ApiException $e) {
                $this->logger->error($e->getMessage(), [
                    'transaction' => $transaction,
                    'mandate_id'  => $mandate_id,
                    'interval'    => $interval,
                    'years'       => $years,
                ]);

                return false;
            }
        }

        // No valid mandates
        $this->logger->error(
            'Cannot create subscription as customer has no valid mandates.',
            [$customer_id]
        );

        return false;
    }

    /**
     * Check the provided customer for valid mandates
     *
     * @param Customer $customer
     * @param string $mandate_id
     *
     * @return bool
     */
    private function check_mandate(Customer $customer, string $mandate_id): bool
    {
        try {
            $mandate = $customer->getMandate($mandate_id);
            if ($mandate->isValid() || $mandate->isPending()) {
                return true;
            }
        } catch (ApiException $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * Syncs Mollie transactions with the local DB.
     * Returns the number of transactions updated.
     *
     * @return int
     */
    public function sync_transactions(): int
    {
        $updated = 0;
        $mapper  = $this->mapper;
        $mapper->get_repository(DonorEntity::class);
        $donors = $mapper->get_all_by();
        /** @var DonorEntity $donor */
        foreach ($donors as $donor) {
            $customer_id = $donor->customer_id;
            if ($donor->mode !== $this->api_mode) {
                $this->set_api_mode($donor->mode);
            }
            $customer = $this->get_customer($customer_id);
            if ($customer) {
                try {
                    $payments = $customer->payments();
                    foreach ($payments as $payment) {
                        $amount   = $payment->amount;
                        $order_id = $payment->metadata->order_id ?? null;
                        $mapper->get_repository(TransactionEntity::class);

                        if ($order_id) {
                            /**
                             * Find existing transaction.
                             * @var TransactionEntity $transaction
                             */
                            $transaction = $mapper->get_one_by([
                                'order_id' => $order_id,
                                'status'   => 'open',
                            ]);

                            if ($transaction) {
                                $transaction->set_fields(
                                    [
                                        'status'          => $payment->status,
                                        'customer_id'     => $payment->customerId,
                                        'value'           => $amount->value,
                                        'currency'        => $amount->currency,
                                        'sequence_type'   => $payment->sequenceType,
                                        'method'          => $payment->method,
                                        'mode'            => $payment->mode,
                                        'subscription_id' => $payment->subscriptionId,
                                        'transaction_id'  => $payment->id,
                                        'campaign_id'     => $payment->metadata ? $payment->metadata->campaign_id : null,
                                    ]
                                );
                                $mapper->save($transaction);
                                do_action("kudos_transaction_$payment->status", $transaction->order_id);
                                $updated++;
                            }
                        }
                    }
                } catch (ApiException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }

        return $updated;
    }

    /**
     * Adds missing transactions from Mollie.
     * Returns the number of transactions added.
     *
     * @return int
     */
    public function add_missing_transactions(): int
    {
        $added  = 0;
        $mapper = $this->mapper;
        $mapper->get_repository(DonorEntity::class);
        $donors = $mapper->get_all_by();
        /** @var DonorEntity $donor */
        foreach ($donors as $donor) {
            $customer_id = $donor->customer_id;
            if ($donor->mode !== $this->api_mode) {
                $this->set_api_mode($donor->mode);
            }
            $customer = $this->get_customer($customer_id);
            if ($customer) {
                try {
                    $payments = $customer->payments();
                    foreach ($payments as $payment) {
                        $order_id = $payment->metadata->order_id ?? null;

                        if ($order_id) {
                            $mapper->get_repository(TransactionEntity::class);

                            /**
                             * Find existing transaction.
                             * @var TransactionEntity $transaction
                             */
                            $transaction = $mapper->get_one_by([
                                'order_id' => $order_id,
                            ]);

                            // Add new transaction if none found.
                            if ( ! $transaction) {
                                $transaction = new TransactionEntity([
                                    'order_id'        => $order_id,
                                    'created'         => $payment->createdAt,
                                    'status'          => $payment->status,
                                    'customer_id'     => $payment->customerId,
                                    'value'           => $payment->amount->value,
                                    'currency'        => $payment->amount->currency,
                                    'sequence_type'   => $payment->sequenceType,
                                    'method'          => $payment->method,
                                    'mode'            => $payment->mode,
                                    'subscription_id' => $payment->subscriptionId,
                                    'transaction_id'  => $payment->id,
                                    'campaign_id'     => $payment->metadata ? $payment->metadata->campaign_id : null,
                                ]);

                                $mapper->save($transaction);
                                $added++;
                            }
                        }
                    }
                } catch (ApiException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }

        return $added;
    }

    /**
     * Returns the vendor name.
     *
     * @return string
     */
    public function __toString(): string
    {
        return self::get_vendor_name();
    }

    /**
     * Returns the current vendor name.
     *
     * @return string
     */
    public static function get_vendor_name(): string
    {
        return self::VENDOR_NAME;
    }
}