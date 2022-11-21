<?php

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Controller\Rest\Route\Campaign;
use IseardMedia\Kudos\Controller\Rest\Route\Mail;
use IseardMedia\Kudos\Controller\Rest\Route\Payment;
use IseardMedia\Kudos\Controller\Rest\Route\Transaction;

class RestRoutes
{
    /**
     * @var \IseardMedia\Kudos\Controller\Rest\Route\Transaction
     */
    private $transaction;
    /**
     * @var \IseardMedia\Kudos\Controller\Rest\Route\Payment
     */
    private $payment;
    /**
     * @var \IseardMedia\Kudos\Controller\Rest\Route\Mail
     */
    private $mail;
    /**
     * @var \IseardMedia\Kudos\Controller\Rest\Route\Campaign
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
