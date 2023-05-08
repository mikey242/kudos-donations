<?php

namespace Kudos\Controller\Rest;

use Kudos\Controller\Rest\Route\Mail;
use Kudos\Controller\Rest\Route\Payment;
use Kudos\Controller\Rest\Route\Transaction;

class RestRoutes {

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

	public function __construct( Mail $mail, Payment $payment, Transaction $transaction ) {

		$this->mail        = $mail;
		$this->payment     = $payment;
		$this->transaction = $transaction;

	}

	public function register_all() {

		$this->mail->register();
		$this->payment->register();
		$this->transaction->register();
	}
}
