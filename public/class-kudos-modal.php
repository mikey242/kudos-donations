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
	 * Kudos_Modal constructor.
     *
	 * @since      1.0.0
	 */
	public function __construct() {

	    $this->logger = new Kudos_Logger();
	    $this->ready = Kudos_Public::ready();
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
			    'text' => $text
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
                    'name_placeholder' => __('Name', 'kudos-donations'),
                    'email_placeholder' => __('E-mail address', 'kudos-donations'),
                    'amount_placeholder' => __('Amount', 'kudos-donations'),
		            'name_required' => get_option('_kudos_name_required'),
		            'email_required' => get_option('_kudos_email_required'),
                    'secure_payment' => __('Secure payment by', 'kudos-donations'),
                    'cancel' => __('Cancel', 'kudos-donations'),
                    'donate' => __('Donate', 'kudos-donations')
		    ];
			return $this->twig->render('/public/modal/donate.modal.html.twig', $data);
		}
		return false;
	}
}