<?php

namespace Kudos\Front;

use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\TwigService;

class KudosModal {

	/**
	 * @var LoggerService
	 */
	private $logger;
	/**
	 * @var TwigService
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

		$this->color = Settings::get_setting('theme_color');
	    $this->logger = new LoggerService();
	    $this->returnUrl = Utils::get_return_url();
	    $this->twig = new TwigService();

    }

    private function get_modal($template, $data) {

	    return $this->twig->render($template, $data);

    }

	/**
	 * Get message modal markup
	 *
	 * @param $atts
	 * @return string|bool
	 * @since      1.0.0
	 */
	public function get_message_modal($atts) {

		    $data = [
			    'header' => $atts['header'],
			    'text' => $atts['text'],
			    'color' => $this->color
		    ];

		    return $this->get_modal('/public/modal/message.modal.html.twig', $data);

    }

	/**
	 * Get the donate modal markup
	 *
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_donate_modal($echo=true) {

		$privacy_option = Settings::get_setting("privacy_link");
		$privacy_link = __('I agree with the privacy policy.', "kudos-donations");
		if($privacy_option) {
			$privacy_link = sprintf(__('I agree with the %s', "kudos-donations"), '<a target="_blank" href=' . Settings::get_setting("privacy_link") . '>' . __("privacy policy", "kudos-donations") . '</a>.');
		}

		$data = [
			'modal_id' => $this->id,
			'color' => $this->color,
			'return_url' => Utils::get_return_url(),
			'nonce' => wp_nonce_field('kudos_submit', '_wpnonce', true, false),
			'privacy_link' => $privacy_link,
			'header' => $this->header,
			'text' => $this->text,
			'amount' => [
				'type'  => $this->amount_type,
				'fixed_values' =>explode(',', $this->fixed_amounts)
			],
			'donation_label' => $atts['donation_label'] ?? get_the_title(),
			'payment_by' => __('Secure payment by', 'kudos-donations'),

			// Global settings
			'vendor' => Settings::get_setting('payment_vendor'),
			'address' => [
				'enabled' => Settings::get_setting('address_enabled'),
				'required' => Settings::get_setting('address_required')
			]
		];

		$out = $this->twig->render('/public/modal/donate.modal.html.twig', $data);

		if($echo) {
			echo $out;
		}

		return $out;

	}
}