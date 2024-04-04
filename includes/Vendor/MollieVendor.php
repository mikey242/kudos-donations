<?php
/**
 * Mollie payment vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Vendor;

use Exception;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\AbstractService;
use IseardMedia\Kudos\Service\SettingsService;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Resources\SubscriptionCollection;
use Mollie\Api\Types\RefundStatus;
use Mollie\Api\Types\SequenceType;
use Psr\Log\LoggerInterface;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class MollieVendor extends AbstractService implements VendorInterface
{
    /**
     * The API mode (test or live).
     *
     * @var string
     */
    private string $api_mode = 'test';
    /**
     * @var array
     */
    private array $api_keys;
	private LoggerInterface $logger;
	private MollieApiClient $api_client;
	private SettingsService $settings;

	/**
     * Mollie constructor.
     */
    public function __construct( LoggerInterface $logger, MollieApiClient $api_client, SettingsService $settings)
    {
	    $this->logger     = $logger;
	    $this->api_client = $api_client;
	    $this->settings   = $settings;
    }

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$settings         = $this->settings->get_setting(SettingsService::SETTING_NAME_VENDOR_MOLLIE);
		$this->api_mode   = $settings['mode'] ?? 'test';
		$this->api_keys   = [
			'test' => $settings['test_key']['key'] ?? '',
			'live' => $settings['live_key']['key'] ?? '',
		];

		$this->config_client($this->api_mode);
		$this->set_user_agent();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_init', 'init' ];
	}

	/**
	 * Returns the current vendor name.
	 *
	 * @return string
	 */
	public static function get_vendor_name(): string
	{
		return 'Mollie';
	}

	public static function get_vendor_slug(): string
	{
		return 'mollie';
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
	 * {@inheritDoc}
	 */
	public function is_ready(): bool {
		$settings = $this->settings->get_current_vendor_settings();
		$mode = $this->api_mode;
		return $settings[$mode . '_key']['verified'] ?? false;
	}

    /**
     * Change the API client to the key for the specified mode.
     */
    private function config_client(?string $mode): void {
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
    private function set_user_agent(): void {
        global $wp_version;
        $this->api_client->addVersionString("KudosDonations/" . KUDOS_VERSION);
        $this->api_client->addVersionString("WordPress/" . $wp_version);
    }

	/**
	 * {@inheritDoc}
	 */
    public static function supports_recurring(): bool
    {
        return true;
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
	 * {@inheritDoc}
	 */
	public function verify_connection($data ): WP_REST_Response {
		return $this->check_api_keys($data);
	}

	/**
     * Check the Mollie api keys for both test and live keys. Sends a JSON response.
     */
    public function check_api_keys(WP_REST_Request $request): WP_REST_Response {
	    $keys = $request->get_param( 'keys' );

	    // Check if key(s) are empty.
	    if (empty($keys)) {
			return new WP_REST_Response([
				'success' => false,
				'message' => __('Please enter an API key.', 'kudos-donations')
			], 400);
	    }

	    $settings = $this->settings->get_current_vendor_settings();

	    $api_keys = [];
		foreach ($keys as $type => $value) {
			if ($value) {

				// Set verified to false.
				$this->settings->update_setting(SettingsService::SETTING_NAME_VENDOR_MOLLIE, array_merge([
					$type . '_key' => [
						'verified' => false
					],
				], $settings));

				// Check that the api key corresponds to each mode.
				if (substr($value, 0, 5) === $type . "_") {
					// Set local key.
					$this->api_keys[$type] = $value;

					// Test key with Mollie.
					$verified = $this->refresh_api_connection($value);

					if(!$verified) {
						return new WP_REST_Response([
							'success' => false,
							/* translators: %s: API mode */
							'message' => sprintf(
								__('%1$s API key is invalid.', 'kudos-donations'),
								ucfirst($type)
							),
						], 400);
					}

					// Update settings array.
					$api_keys[$type . '_key'] = [
						'verified' => true,
						'key' => $value
					];
				} else {
					return new WP_REST_Response([
						'success' => false,
						/* translators: %s: API mode */
						'message' => sprintf(
							__('%1$s API key should begin with %2$s', 'kudos-donations'),
							ucfirst($type),
							$type . '_'
						),
					], 400);
				}
			}
		}

		$current_settings = $this->settings->get_current_vendor_settings();
		$updated_settings = array_merge([
			'recurring'       => $this->can_use_recurring(),
			'mode'            => $this->api_mode,
			'payment_methods' => array_map(function ($method) {
				return [
					'id'            => $method->id,
					'status'        => $method->status,
					'maximumAmount' => (array)$method->maximumAmount,
				];
			}, (array)$this->get_payment_methods())
		], $api_keys);

		$combined_settings = array_merge($current_settings, $updated_settings);

        // Update vendor settings.
		$this->settings->update_setting(
			SettingsService::SETTING_NAME_VENDOR_MOLLIE,
			$combined_settings
		);

	    return new WP_REST_Response([
		    'success' => true,
		    'message' =>
		    /* translators: %s: API mode */
			    __('API connection was successful!', 'kudos-donations'),
	    ], 200);
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
     * Gets a list of payment methods for the current Mollie account
     *
     * @param array $options https://docs.mollie.com/reference/v2/methods-api/list-methods
     *
     * @return BaseCollection|MethodCollection|null
     */
    public function get_payment_methods(array $options = []) {
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
    public function get_subscriptions(string $customer_id) {
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
     * @param WP_Post $subscription Instance of WP_Post.
     *
     * @return bool
     */
    public function cancel_subscription( WP_Post $subscription): bool
    {
		$transaction = get_post($subscription->{SubscriptionPostType::META_FIELD_TRANSACTION_ID});
		$customer_id = $transaction->{TransactionPostType::META_FIELD_VENDOR_CUSTOMER_ID};
	    $customer = $this->get_customer($customer_id);

        // Bail if no subscription found locally or if not active.
        if ('active' !== $subscription->{SubscriptionPostType::META_FIELD_STATUS} || null === $customer) {
            return false;
        }

        // Cancel the subscription via Mollie's API.
        try {
            $response = $customer->cancelSubscription($subscription->{SubscriptionPostType::META_FIELD_SUBSCRIPTION_ID});

            /** @var Subscription $response */
            return ($response->status === PaymentStatus::CANCELED);
        } catch (ApiException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Get the customer from Mollie.
     *
     * @param string $vendor_customer_id
     *
     * @return Customer|null
     */
    public function get_customer($vendor_customer_id): ?Customer
    {
        try {
            return $this->api_client->customers->get($vendor_customer_id);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return null;
        }
    }

    /**
     * Create a Mollie customer.
     *
     * @param string $email Donor email address.
     * @param string $name Donor name.
     *
     * @return bool|Customer
     */
    public function create_customer(string $email, string $name) {
        $args = [
            'email' => $email,
        ];

        if ($name) {
            $args['name'] = $name;
        }

        try {
            return $this->api_client->customers->create($args);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

	/**
	 * {@inheritDoc}
	 */
    public function create_payment(array $payment_args, int $transaction_id, ?string $vendor_customer_id): string {

		$transaction = get_post($transaction_id);

        // Set payment frequency.
        $payment_args['payment_frequency'] = "true" === $payment_args['recurring'] ? $payment_args['recurring_frequency'] : SequenceType::SEQUENCETYPE_ONEOFF;
	    $sequence_type                     = "true" === $payment_args['recurring'] ? SequenceType::SEQUENCETYPE_FIRST : SequenceType::SEQUENCETYPE_ONEOFF;
        $payment_args['value']             = number_format($payment_args['value'], 2, '.', '');
        $redirect_url                      = $payment_args['return_url'];

        // Add order id query arg to return url if option to show message enabled.
        try {
			$show_return_message = get_post_meta($payment_args['campaign_id'], 'show_return_message', true);
            if ( ! empty($show_return_message)) {
                $action       = 'order_complete';
                $redirect_url = add_query_arg(
                    [
                        'kudos_action'   => 'order_complete',
                        'kudos_transaction_id' => $transaction_id,
                        'kudos_nonce'    => wp_create_nonce($action . $transaction_id),
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
            'description'  => $transaction->post_title,
            'metadata'     => [
                SubscriptionPostType::META_FIELD_TRANSACTION_ID => $transaction_id,
                SubscriptionPostType::META_FIELD_FREQUENCY      => $payment_args['payment_frequency'],
                SubscriptionPostType::META_FIELD_YEARS          => $payment_args['recurring_length'],
                DonorPostType::META_FIELD_EMAIL                 => $payment_args['email'],
                DonorPostType::META_FIELD_NAME                  => $payment_args['name'],
                TransactionPostType::META_FIELD_CAMPAIGN_ID     => $payment_args['campaign_id'],
            ],
        ];

        // Link payment to customer if specified.
        if ($vendor_customer_id) {
            $payment_array['customerId'] = $vendor_customer_id;
        }

        try {
            $payment = $this->api_client->payments->create($payment_array);

            $this->logger->info(
                "New " . $this->get_vendor_name() . " payment created.",
                ['transaction_id' => $transaction_id, 'sequence_type' => $payment->sequenceType]
            );

            return $payment->getCheckoutUrl();
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return '';
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

        // Otherwise, return normal rest URL.
        return get_rest_url(null, $route);
    }

    /**
     * Mollie webhook handler.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function rest_webhook(WP_REST_Request $request) {
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
            "Webhook requested by " . $this::get_vendor_name(),
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
                    'vendor_id'      => $payment_id,
                    'status'         => $payment->status,
                    'sequence_type'  => $payment->sequenceType,
                    'has_refunds'    => $payment->hasRefunds(),
                ]
            );

            /**
             * Create new transaction if this is a recurring payment.
             * e.g. New recurring payment.
             */
            if ( $payment->hasSequenceTypeRecurring()) {
                $this->logger->debug('Recurring payment received, creating transaction.', [
                    'subscription_id' => $payment->subscriptionId,
                ]);
                $customer     = $mollie->customers->get($payment->customerId);
                $subscription = $customer->getSubscription($payment->subscriptionId);
                $transaction  = TransactionPostType::save(
					[
						TransactionPostType::META_FIELD_DONOR_ID => $subscription->metadata->{TransactionPostType::META_FIELD_DONOR_ID} ?? '',
	                    TransactionPostType::META_FIELD_CAMPAIGN_ID => $subscription->metadata->{TransactionPostType::META_FIELD_CAMPAIGN_ID} ?? '',
                    ]
                );
            } else {
	            $transaction = get_post($payment->metadata->transaction_id);
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
                    ['vendor_id' => $payment_id]
                );

                return $response;
            }

            // Update transaction status.
	        TransactionPostType::save([
				'ID' => $transaction->ID,
				TransactionPostType::META_FIELD_STATUS => $payment->status
	        ]);

            // Create action with order_id as parameter.
            do_action("kudos_transaction_$payment->status", $transaction->ID);

            if ($payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks()) {
                /*
                 * The payment is paid and isn't refunded or charged back.
                 * Time to check if this is a duplicate before processing.
                 */
                if ($payment_id === $transaction->ID) {
                    $this->logger->debug('Duplicate webhook detected. Ignoring', ['transaction_id' => $payment_id]);

                    return $response;
                }

                // Update transaction.
	            TransactionPostType::save([
					'ID' => $transaction->ID,
		            TransactionPostType::META_FIELD_STATUS                => $payment->status,
		            TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID     => $payment->id,
		            TransactionPostType::META_FIELD_VENDOR_CUSTOMER_ID    => $payment->customerId,
		            TransactionPostType::META_FIELD_VALUE                 => $payment->amount->value,
		            TransactionPostType::META_FIELD_CURRENCY              => $payment->amount->currency,
		            TransactionPostType::META_FIELD_SEQUENCE_TYPE         => $payment->sequenceType,
		            TransactionPostType::META_FIELD_METHOD                => $payment->method,
		            TransactionPostType::META_FIELD_MODE                  => $payment->mode,
		            TransactionPostType::META_FIELD_SUBSCRIPTION_ID       => $payment->subscriptionId,
	            ]);

                // Set up recurring payment if sequence is first.
                if ($payment->hasSequenceTypeFirst()) {
                    $this->logger->info('Creating subscription.', $transaction->to_array());
                    $this->create_subscription(
                        $transaction,
                        $payment->mandateId,
                        $payment->metadata->{SubscriptionPostType::META_FIELD_FREQUENCY},
	                    (int) $payment->metadata->{SubscriptionPostType::META_FIELD_YEARS}
                    );
                }
            } elseif ($payment->hasRefunds()) {
                /*
                 * The payment has been (partially) refunded.
                 * The status of the payment is still "paid".
                 */
                do_action('kudos_mollie_refund', $transaction->ID);

	            // Update transaction.
	            TransactionPostType::save([
		            'ID' => $transaction->ID,
		            TransactionPostType::META_FIELD_REFUNDS => json_encode(
			            [
				            'refunded'  => $payment->getAmountRefunded(),
				            'remaining' => $payment->getAmountRemaining(),
			            ]
		            ),
	            ]);

                $this->logger->info('Payment refunded.', ['transaction' => $transaction]);
            }
        } catch (ApiException $e) {
            $this->logger->error($this::get_vendor_name() . " webhook exception: " . $e->getMessage(), ['payment_id' => $payment_id]);

            // Send fail response to Mollie so that they know to try again.
            return rest_ensure_response(
                new WP_REST_Response([
                    'success' => false,
                    'id'      => $payment_id,
                ], 500)
            );
        }

        return $response;
    }

    /**
     * Creates a subscription based on the provided transaction
     *
     * @return false|Subscription
     */
    public function create_subscription(
        WP_Post $transaction,
        string $mandate_id,
        string $interval,
        int $years
    ) {
		$donor       = get_post($transaction->{TransactionPostType::META_FIELD_DONOR_ID});
		$customer_id = $donor->{DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID};
        $start_date  = gmdate('Y-m-d', strtotime('+' . $interval));
        $currency    = 'EUR';
        $value       = number_format( (int) $transaction->{TransactionPostType::META_FIELD_VALUE}, 2);

        $subscription_array = [
            'amount'      => [
                'value'    => $value,
                'currency' => $currency,
            ],
            'webhookUrl'  => $this->get_webhook_url(),
            'mandateId'   => $mandate_id,
            'interval'    => $interval,
            'startDate'   => $start_date,
	        'description' => apply_filters('kudos_subscription_description', __('Subscription', 'kudos-donations') .
                sprintf(' (%1$s) - %2$s', $interval, SubscriptionPostType::get_formatted_id($transaction->ID)),
	        ),
            'metadata'    => [
                TransactionPostType::META_FIELD_CAMPAIGN_ID => $transaction->{TransactionPostType::META_FIELD_CAMPAIGN_ID},
	            TransactionPostType::META_FIELD_DONOR_ID => $transaction->{TransactionPostType::META_FIELD_DONOR_ID}
            ],
        ];

        if ('test' === $transaction->{TransactionPostType::META_FIELD_MODE}) {
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
				SubscriptionPostType::save([
					SubscriptionPostType::META_FIELD_STATUS => $subscription->status,
					SubscriptionPostType::META_FIELD_FREQUENCY => $interval,
					SubscriptionPostType::META_FIELD_YEARS => $years,
					SubscriptionPostType::META_FIELD_VALUE => $value,
					SubscriptionPostType::META_FIELD_CURRENCY => $currency,
					SubscriptionPostType::META_FIELD_SUBSCRIPTION_ID => $subscription->id,
					SubscriptionPostType::META_FIELD_TRANSACTION_ID => $transaction->ID
				]);

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
            __('Cannot create subscription as customer has no valid mandates.', 'kudos-donations'),
            [$customer_id]
        );

        return false;
    }

    /**
     * Check the provided customer for valid mandates.
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
	 * {@inheritDoc}
	 */
	public function refund( int $post_id ): bool {
		$post = get_post($post_id);
		if(TransactionPostType::get_slug() === $post->post_type) {
			$payment_id = $post->{TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID};
			$amount['value'] = number_format((float) $post->{TransactionPostType::META_FIELD_VALUE}, 2, '.', '');
			$amount['currency'] = $post->{TransactionPostType::META_FIELD_CURRENCY};
			try {
				$payment = $this->api_client->payments->get($payment_id);
				$response = $payment->refund(["amount" => $amount]);
				$this->logger->info(sprintf('Refunding transaction "%s"', $payment_id), ["status" => $response->status, 'amount' => $amount]);
				if(RefundStatus::STATUS_PENDING == $response->status) {
					return true;
				}
				return false;
			} catch (ApiException $e) {
				$this->logger->error($e->getMessage());
			}
		}
		return false;
	}
}
