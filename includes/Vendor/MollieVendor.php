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

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\EncryptionService;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Types\RefundStatus;
use Mollie\Api\Types\SequenceType;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class MollieVendor extends AbstractRegistrable implements VendorInterface, HasSettingsInterface
{
	public const SETTING_API_MODE = '_kudos_vendor_mollie_api_mode';
	public const SETTING_RECURRING = '_kudos_vendor_mollie_recurring';
	public const SETTING_API_KEY_LIVE = '_kudos_vendor_mollie_api_key_live';
	public const SETTING_API_KEY_TEST = '_kudos_vendor_mollie_api_key_test';
	public const SETTING_API_KEY_ENCRYPTED_LIVE = '_kudos_vendor_mollie_api_key_encrypted_live';
	public const SETTING_API_KEY_ENCRYPTED_TEST = '_kudos_vendor_mollie_api_key_encrypted_test';
	public const SETTING_PAYMENT_METHODS = '_kudos_vendor_mollie_payment_methods';

	/**
     * The API mode (test or live).
     *
     * @var string
     */
    private string $api_mode = 'test';
	private MollieApiClient $api_client;
	private EncryptionService $encryption;

	/**
     * Mollie constructor.
     */
    public function __construct( MollieApiClient $api_client, EncryptionService $encryption )
    {
	    $this->api_client = $api_client;
		$this->encryption = $encryption;

		// Handle API key saving.
	    add_filter( 'pre_update_option_' . self::SETTING_API_KEY_LIVE, [ $this, 'handle_key_update' ], 10, 3 );
	    add_filter( 'pre_update_option_' . self::SETTING_API_KEY_TEST, [ $this, 'handle_key_update' ], 10, 3 );
    }

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->api_mode   = get_option( self::SETTING_API_MODE );
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
	 * {@inheritDoc}
	 */
	public static function get_vendor_name(): string
	{
		return 'Mollie';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_vendor_slug(): string
	{
		return 'mollie';
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_ready(): bool {
		$keys = $this->get_decrypted_api_keys();
		$mode = $this->api_mode;
		return !empty($keys[$mode]) ?? false;
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
     * Change the API client to the key for the specified mode.
     */
    private function config_client(?string $mode): void {
        $this->api_mode = $mode;
        // Gets the key associated with the specified mode.
	    $key = $this->get_decrypted_api_keys()[$mode] ?? false;

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
     * @see https://docs.mollie.com/docs/integration-partners-user-agent-strings
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
	private function can_use_recurring(): bool
	{
		$methods = $this->get_active_payment_methods([
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
	public function refresh_api(): WP_REST_Response {

		// Rebuild Mollie settings.
		$payment_methods = array_map(function (Method $method) {
			return [
				'id'            => $method->id,
				'description'   => $method->description,
				'image'         => $method->image->svg,
				'minimumAmount' => $method->minimumAmount,
				'maximumAmount' => (array)$method->maximumAmount,
			];
		}, (array)$this->get_active_payment_methods());

		if(empty($payment_methods)) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' =>
						__( 'No payment methods found. Please check your API keys', 'kudos-donations' ),
				],
				200
			);
		}

		$this->logger->debug('Mollie refreshed connection settings', [$payment_methods]);

		// Update payment methods.
		update_option(
			self::SETTING_PAYMENT_METHODS,
			$payment_methods
		);

		// Update recurring status.
		update_option(self::SETTING_RECURRING, $this->can_use_recurring());

		return new WP_REST_Response(
			[
				'success' => true,
				'message' =>
					__( 'Payment methods refreshed', 'kudos-donations' ),
			],
			200
		);
	}

	/**
	 * Returns the decrypted API keys.
	 *
	 * @return array
	 */
	private function get_decrypted_api_keys(): array {
		$api_keys = [];
		foreach (['live', 'test'] as $key) {
			add_filter( 'option_' . constant("self::SETTING_API_KEY_ENCRYPTED_" . strtoupper($key)), [ $this->encryption, 'decrypt_password' ] );
			$api_keys[$key] = get_option( constant("self::SETTING_API_KEY_ENCRYPTED_" . strtoupper($key)) );
			remove_filter('option_' . constant("self::SETTING_API_KEY_ENCRYPTED_" . strtoupper($key)), [ $this->encryption, 'decrypt_password' ]);
		}
		return $api_keys;
	}

	/**
	 * Handles the saving of the test and live api keys.
	 *
	 * @param string $value The new value.
	 * @param string $old_value The previous value.
	 * @param string $option The option name.
	 *
	 * @return string|WP_Error
	 */
	public function handle_key_update( string $value, string $old_value, string $option) {

		// Determine whether the option is LIVE or TEST.
		if($option === self::SETTING_API_KEY_LIVE) {
			$mode = 'live';
		} else {
			$mode = 'test';
		}

		// Clear value.
		if(!$value) {
			update_option(constant("self::SETTING_API_KEY_ENCRYPTED_" . strtoupper($mode) ),'');
			update_option(self::SETTING_PAYMENT_METHODS, []);
			return $value;
		} else {
			$result = $this->verify_key($value, $mode);
			if(is_wp_error($result)) {
				return rest_ensure_response($result);
			}
			$this->refresh_api();
			return $result;
		}
	}

	/**
	 * Encrypts the raw key and saves it in another setting.
	 *
	 * @param string $raw_key The unencrypted key.
	 * @param string $mode The api mode the key is for.
	 *
	 * @return string
	 */
	private function verify_key( string $raw_key, string $mode): string {
		// Bail if this is only asterisks.
		$num_asterisks = substr_count( $raw_key, '*' );
		$count         = \strlen( $raw_key );
		if ( $num_asterisks !== $count ) {
			// Check that key works before saving.
			$key_valid = apply_filters("kudos_mollie_" . $mode . "_key_validation", $this->is_key_valid($raw_key));
			if($key_valid) {
				$encrypted_key = $this->encryption->encrypt_password($raw_key);
				$raw_key = str_repeat( '*', strlen($raw_key) );
				update_option(constant("self::SETTING_API_KEY_ENCRYPTED_" . strtoupper($mode) ), $encrypted_key);
			} else {
				wp_send_json_error(
					// translators: %s is the API mode (test or live).
					['message' => wp_sprintf(__('%s API key invalid.', 'kudos-donations'), $mode)],
					400
				);
			}
		}
		return $raw_key;
	}

    /**
     * Checks the provided api key by attempting to get associated payments.
     *
     * @param string $api_key API key to test.
     *
     * @return bool
     */
    private function is_key_valid(string $api_key): bool
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
    public function get_active_payment_methods(array $options = []) {
        try {
            return $this->api_client->methods->allActive($options);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());

            return null;
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
            $response = $customer->cancelSubscription($subscription->{SubscriptionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID});

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
    public function get_customer( string $vendor_customer_id): ?Customer
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
    public function create_payment(array $payment_args, int $transaction_id, ?string $vendor_customer_id) {

		$transaction = get_post($transaction_id);

        // Set payment frequency.
        $payment_args['payment_frequency'] = "true" === $payment_args['recurring'] ? $payment_args['recurring_frequency'] : SequenceType::SEQUENCETYPE_ONEOFF;
	    $sequence_type                     = "true" === $payment_args['recurring'] ? SequenceType::SEQUENCETYPE_FIRST : SequenceType::SEQUENCETYPE_ONEOFF;
        $payment_args['value']             = number_format(floatval($payment_args['value']), 2, '.', '');
        $redirect_url                      = $payment_args['return_url'];

        // Add order id query arg to return url if option to show message enabled.
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

			// Checkout URL used to complete payment.
			$checkout_url =$payment->getCheckoutUrl();

			// Update meta field from payment object.
			update_post_meta($transaction_id, TransactionPostType::META_FIELD_CHECKOUT_URL, $checkout_url);

            return $checkout_url;
        } catch (ApiException $e) {
            $this->logger->error('Error creating payment with Mollie', ['error' =>$e->getMessage()]);
            return false;
        }
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
		$currency    = $transaction->{TransactionPostType::META_FIELD_CURRENCY};
		$value       = Utils::format_value_for_use($transaction->{TransactionPostType::META_FIELD_VALUE});
		$customer    = $this->get_customer($customer_id);

		// Create subscription if valid mandate found.
		if ($this->check_mandate($customer, $mandate_id)) {
			try {

				// Create subscription post.
				$subscription_post = SubscriptionPostType::save([
					SubscriptionPostType::META_FIELD_FREQUENCY              => $interval,
					SubscriptionPostType::META_FIELD_YEARS                  => $years,
					SubscriptionPostType::META_FIELD_VALUE                  => $value,
					SubscriptionPostType::META_FIELD_CURRENCY               => $currency,
					SubscriptionPostType::META_FIELD_TRANSACTION_ID         => $transaction->ID
				]);

				// Prepare arguments to send to Mollie.
				$subscription_args = [
					'amount'      => [
						'value'    => $value,
						'currency' => $currency,
					],
					'webhookUrl'  => $this->get_webhook_url(),
					'mandateId'   => $mandate_id,
					'interval'    => $interval,
					'startDate'   => $start_date,
					'description' => $subscription_post->post_title,
					'metadata'    => [
						TransactionPostType::META_FIELD_CAMPAIGN_ID => $transaction->{TransactionPostType::META_FIELD_CAMPAIGN_ID},
						TransactionPostType::META_FIELD_DONOR_ID => $transaction->{TransactionPostType::META_FIELD_DONOR_ID}
					],
				];

				// Disable startDate for test mode.
				if ('test' === $transaction->{TransactionPostType::META_FIELD_MODE}) {
					unset($subscription_args['startDate']);
				}

				if ($years && $years > 0) {
					$subscription_args['times'] = Utils::get_times_from_years($years, $interval);
				}

				$subscription       = $customer->createSubscription($subscription_args);

				// Update subscription post with status and subscription id.
				SubscriptionPostType::save([
					'ID' => $subscription_post->ID,
					SubscriptionPostType::META_FIELD_STATUS                 => $subscription->status,
					SubscriptionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID => $subscription->id,
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

		// No valid mandates.
		$this->logger->error(
			__('Cannot create subscription as customer has no valid mandates.', 'kudos-donations'),
			[$customer_id]
		);

		return false;
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

				//	Get post id if $campaign_id is slug from pre 4.0.0 version.
				$campaign_id = $subscription->metadata->{TransactionPostType::META_FIELD_CAMPAIGN_ID};
				$campaign = CampaignPostType::get_post_by_id_or_slug($campaign_id);
				$campaign_id = $campaign->ID;

				// Get Donor ID. If subscription from pre 4.0.0, use customerId to get new donor ID.
	            $donor_id = $subscription->metadata->{TransactionPostType::META_FIELD_DONOR_ID}
	                        ?? DonorPostType::get_post([DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID => $subscription->customerId])->ID ?? null;

				// Save new transaction.
                $transaction  = TransactionPostType::save(
					[
						TransactionPostType::META_FIELD_DONOR_ID => $donor_id,
	                    TransactionPostType::META_FIELD_CAMPAIGN_ID => $campaign_id ?? '',
						TransactionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID => $subscription->id
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

            // Create action with post id as parameter.
            do_action("kudos_transaction_$payment->status", $transaction->ID);

            if ($payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks()) {
                /*
                 * The payment is paid and isn't refunded or charged back.
                 * If it already has an ID then it has already been processed.
                 */
                if ($payment_id === $transaction->ID) {
                    $this->logger->debug('Duplicate webhook detected. Ignoring', ['transaction_id' => $payment_id]);

                    return $response;
                }

                // Update transaction.
	            TransactionPostType::save([
					'ID'                                                   => $transaction->ID,
		            TransactionPostType::META_FIELD_STATUS                 => $payment->status,
		            TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID      => $payment->id,
		            TransactionPostType::META_FIELD_VENDOR_CUSTOMER_ID     => $payment->customerId,
		            TransactionPostType::META_FIELD_VALUE                  => $payment->amount->value,
		            TransactionPostType::META_FIELD_CURRENCY               => $payment->amount->currency,
		            TransactionPostType::META_FIELD_SEQUENCE_TYPE          => $payment->sequenceType,
		            TransactionPostType::META_FIELD_METHOD                 => $payment->method,
		            TransactionPostType::META_FIELD_MODE                   => $payment->mode
	            ]);

                // Set up recurring payment if sequence is first.
                if ($payment->hasSequenceTypeFirst()) {
                    $this->logger->info('Creating subscription.', $transaction->to_array());
                    $subscription = $this->create_subscription(
                        $transaction,
                        $payment->mandateId,
                        $payment->metadata->{SubscriptionPostType::META_FIELD_FREQUENCY},
	                    (int) $payment->metadata->{SubscriptionPostType::META_FIELD_YEARS}
                    );
	                // Update transaction with subscription ID.
	                TransactionPostType::save([
		                'ID'                                                   => $transaction->ID,
		                TransactionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID => $subscription->id
	                ]);
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
			$amount['value'] = Utils::format_value_for_use($post->{TransactionPostType::META_FIELD_VALUE});
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

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_API_MODE => [
				'type' => FieldType::STRING,
				'show_in_rest' => true,
				'default' => 'test'
			],
			self::SETTING_API_KEY_TEST => [
				'type' => FieldType::STRING,
				'show_in_rest' => true,
				'default' => ''
			],
			self::SETTING_API_KEY_LIVE => [
				'type' => FieldType::STRING,
				'show_in_rest' => true,
				'default' => ''
			],
			self::SETTING_API_KEY_ENCRYPTED_LIVE => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_API_KEY_ENCRYPTED_TEST => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_RECURRING => [
				'type' => FieldType::BOOLEAN,
				'show_in_rest' => true,
				'default' => false
			],
			self::SETTING_PAYMENT_METHODS => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => [
					'schema'          => [
						'type'        => FieldType::ARRAY,
						'items'       => [
							'type'       => FieldType::OBJECT,
							'properties' => [
								'id' => [
									'type'        => FieldType::STRING,
								],
								'description' => [
									'type' => FieldType::STRING
								],
								'image' => [
									'type' => FieldType::STRING
								],
								'minimumAmount' => [
									'type'        => FieldType::OBJECT,
									'properties'  => [
										'value'    => [
											'type'        => FieldType::STRING,
										],
										'currency' => [
											'type'        => FieldType::STRING,
										],
									],
								],
								'maximumAmount' => [
									'type'        => FieldType::OBJECT,
									'properties'  => [
										'value'    => [
											'type'        => FieldType::STRING,
										],
										'currency' => [
											'type'        => FieldType::STRING,
										],
									],
								],
							],
						],
					],
				],
				'default' => []
			]
		];
	}
}
