<?php

namespace Kudos\Controller\Rest;

use Kudos\Controller\Rest\Route\Campaign;
use Kudos\Controller\Rest\Route\Mail;
use Kudos\Controller\Rest\Route\Payment;
use Kudos\Controller\Rest\Route\Transaction;

class RestRoutes
{
    /**
     * @var \Kudos\Controller\Rest\Route\Transaction
     */
    private $transaction;
    /**
     * @var \Kudos\Controller\Rest\Route\Payment
     */
    private $payment;
    /**
     * @var \Kudos\Controller\Rest\Route\Mail
     */
    private $mail;
    /**
     * @var \Kudos\Controller\Rest\Route\Campaign
     */
    private $campaign;

    public function __construct(
        Mail $mail,
        Payment $payment,
        Transaction $transaction,
        Campaign $campaign
    ) {
        $this->mail        = $mail;
        $this->payment     = $payment;
        $this->transaction = $transaction;
        $this->campaign    = $campaign;
    }

    public function register_all()
    {
        $this->mail->register();
        $this->payment->register();
        $this->transaction->register();
        $this->campaign->register();
    }
}
