<?php
/**
 * Rest routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Controller\Rest;

use Kudos\Controller\Rest\Route\Mail;
use Kudos\Controller\Rest\Route\Payment;
use Kudos\Controller\Rest\Route\Transaction;

class RestRoutes {

	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var Payment
	 */
	private $payment;
	/**
	 * @var Mail
	 */
	private $mail;

	/**
	 * @param Mail        $mail Mailer routes.
	 * @param Payment     $payment Payment routes.
	 * @param Transaction $transaction Transaction route.
	 */
	public function __construct( Mail $mail, Payment $payment, Transaction $transaction ) {

		$this->mail        = $mail;
		$this->payment     = $payment;
		$this->transaction = $transaction;
	}

	/**
	 * Register all the routes.
	 */
	public function register_all() {

		$this->mail->register();
		$this->payment->register();
		$this->transaction->register();
	}
}
