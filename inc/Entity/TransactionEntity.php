<?php

namespace IseardMedia\Kudos\Entity;

class TransactionEntity extends AbstractEntity
{
    /**
     * CampaignTable name without prefix
     *
     * @var string
     */
    protected const TABLE = 'kudos_transactions';
    /**
     * Value of donation
     *
     * @var int
     */
    public int $value;
    /**
     * Currency of donation (EUR)
     *
     * @var string
     */
    public string $currency;
    /**
     * Status of transaction
     *
     * @var string
     */
    public string $status;
    /**
     * Payment method
     *
     * @var string
     */
    public string $method;
    /**
     * Mode used ('test' or 'live')
     *
     * @var string
     */
    public string $mode;
    /**
     * Sequence type (oneoff, first, recurring)
     *
     * @var string
     */
    public string $sequence_type;
    /**
     * Mollie transaction id
     *
     * @var string
     */
    public string $transaction_id;
    /**
     * Kudos order id
     *
     * @var string
     */
    public string $order_id;
    /**
     * Mollie customer id
     *
     * @var string
     */
    public string $customer_id;
    /**
     * Mollie subscription id
     *
     * @var string
     */
    public string $subscription_id;
    /**
     * CampaignPostType label for donation
     *
     * @var string
     */
    public string $campaign_id;
    /**
     * Refunds serialized array
     *
     * @var string
     */
    public string $refunds;
    /**
     * Message
     *
     * @var string
     */
    public string $message;

    /**
     * Returns unserialized array of refund data.
     *
     * @return object|false
     */
    public function get_refund(): object|bool {
        $refunds = $this->refunds;

        if ($refunds) {
            $result = json_decode($refunds);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $result;
            }
        }

        return false;
    }
}
