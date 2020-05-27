<?php

namespace Kudos;

class Kudos_Modal {

	/**
	 * @var bool
	 */
	private $ready;
	/**
	 * @var Kudos_Logger
	 */
	private $logger;
	/**
	 * @var Kudos_Twig
	 */
	private $twig;
	/**
	 * @var string|void
	 */
	private $returnUrl;

	/**
	 * Kudos_Modal constructor.
     *
	 * @since      1.0.0
	 */
	public function __construct() {

	    $this->logger = new Kudos_Logger();
	    $this->ready = Kudos_Public::ready();
	    $this->returnUrl = Kudos_Public::get_return_url();
	    $this->twig = new Kudos_Twig();

    }

	/**
	 * Get message modal markup
	 *
	 * @since      1.0.0
	 * @param string|null $header
	 * @param string|null $text
	 *
	 * @return string|bool
	 */
	function get_message_modal($header=null, $text=null) {

		    $data = [
			    'header' => $header,
			    'text' => $text,
		    ];

		    return $this->twig->render('/public/modal/message.modal.html.twig', $data);
    }

	/**
	 * Get payment modal markup
	 *
	 * @since    1.0.0
	 */
	function get_payment_modal() {

		if($this->ready) {
		    $data = [
		            'header' => get_option('_kudos_form_header'),
                    'text' => get_option('_kudos_form_text'),
			        'nonce' => wp_nonce_field('kudos_submit', '_wpnonce', true, false),
		            'name_required' => get_option('_kudos_name_required'),
		            'email_required' => get_option('_kudos_email_required'),
		            'return_url' => $this->returnUrl,
			        'payment_by' => __('Secure payment by', 'kudos-donations'),
			        'vendor' => (get_option('_kudos_payment_vendor') ? get_option('_kudos_payment_vendor') : 'mollie')
		    ];
			return $this->twig->render('/public/modal/donate.modal.html.twig', $data);
		}
		return false;
	}
}