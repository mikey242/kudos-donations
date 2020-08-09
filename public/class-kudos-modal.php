<?php

namespace Kudos;

use Kudos\Service\Twig;
use Kudos\Service\Logger;

class Kudos_Modal {

	/**
	 * @var bool
	 */
	private $ready;
	/**
	 * @var Logger
	 */
	private $logger;
	/**
	 * @var Twig
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
	 * @var false|mixed|void
	 */
	private $color;

	/**
	 * Kudos_Modal constructor.
     *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->color = get_kudos_option('theme_color');
	    $this->logger = new Logger();
	    $this->returnUrl = get_return_url();
	    $this->twig = new Twig();
	    $this->id = uniqid('kudos_modal-');

    }

    public function get_id() {
		return $this->id;
    }

	/**
	 * Get message modal markup
	 *
	 * @param $atts
	 * @return string|bool
	 * @since      1.0.0
	 */
	function get_message_modal($atts) {

		    $data = [
			    'header' => $atts['header'],
			    'text' => $atts['text'],
			    'color' => $this->color
		    ];

		    return $this->twig->render('/public/modal/message.modal.html.twig', $data);
    }

	/**
	 * Get payment modal markup
	 *
	 * @param array $atts
	 * @return bool
	 * @since    1.0.0
	 */
	function get_payment_modal($atts) {

		$privacy_option = get_kudos_option("privacy_link");
		$privacy_link = __('I agree with the privacy policy.', "kudos-donations");
		if($privacy_option) {
			$privacy_link = sprintf(__('I agree with the %s', "kudos-donations"), '<a target="_blank" href=' . get_kudos_option("privacy_link") . '>' . __("privacy policy", "kudos-donations") . '</a>.');
		}

	    $data = [
            'modal_id' => $this->id,
            'color' => $this->color,
            'return_url' => $this->returnUrl,
            'nonce' => wp_nonce_field('kudos_submit', '_wpnonce', true, false),
            'privacy_link' => $privacy_link,
            'header' => $atts['modal_header'],
            'text' => $atts['welcome_text'],
		    'amount' => [
		        'type'  => $atts['amount_type'],
			    'fixed_values' =>explode(',', $atts['fixed_amounts'])
		    ],
		    'donation_label' => $atts['donation_label'],
	        'payment_by' => __('Secure payment by', 'kudos-donations'),

		    // Global settings
	        'vendor' => get_kudos_option('payment_vendor'),
            'address' => [
				'enabled' => get_kudos_option('address_enabled'),
				'required' => get_kudos_option('address_required')
			]
	    ];

		return $this->twig->render('/public/modal/donate.modal.html.twig', $data);

	}
}