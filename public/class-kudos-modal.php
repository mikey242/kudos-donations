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
	 * @var string
	 */
	private $id;

	/**
	 * Kudos_Modal constructor.
     *
	 * @since      1.0.0
	 */
	public function __construct() {

	    $this->logger = new Kudos_Logger();
	    $this->returnUrl = Kudos_Public::get_return_url();
	    $this->twig = new Kudos_Twig();
	    $this->id = uniqid('kudos_modal-');

    }

    public function get_id() {
		return $this->id;
    }

	/**
	 * Get message modal markup
	 *
	 * @param array $atts
	 *
	 * @return string|bool
	 * @since      1.0.0
	 */
	function get_message_modal($atts=[]) {

		    $data = [
			    'header' => $atts['header'],
			    'text' => $atts['text'],
		    ];

		    return $this->twig->render('/public/modal/message.modal.html.twig', $data);
    }

	/**
	 * Get payment modal markup
	 *
	 * @param array $atts
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	function get_payment_modal($atts) {

		$privacy_option = get_option("_kudos_privacy_link");
		$privacy_link = __('I agree with the privacy policy.', "kudos-donations");
		if($privacy_option) {
			$privacy_link = sprintf(__('I agree with the %s', "kudos-donations"), '<a target="_blank" href='. get_option("_kudos_privacy_link") .'>'.__("privacy policy", "kudos-donations"). '</a>.');
		}


	    $data = [
            'modal_id' => $this->id,
            'header' => !empty($atts['header']) ? $atts['header'] : get_option('_kudos_form_header'),
            'text' => !empty($atts['text']) ? $atts['text'] : get_option('_kudos_form_text'),
            'color' => !empty($atts['color']) ? $atts['color'] : get_option('_kudos_button_color'),
	        'nonce' => wp_nonce_field('kudos_submit', '_wpnonce', true, false),
            'address' => [
	            'enabled' => get_option('_kudos_address_enabled'),
	            'required' => get_option('_kudos_address_required')
			],
		    'privacy_link' => $privacy_link,
            'return_url' => $this->returnUrl,
		    'donation_label' => $atts['donation_label'],
	        'payment_by' => __('Secure payment by', 'kudos-donations'),
	        'vendor' => (get_option('_kudos_payment_vendor') ? get_option('_kudos_payment_vendor') : 'mollie')
	    ];

		return $this->twig->render('/public/modal/donate.modal.html.twig', $data);

	}
}