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
	 * @var false|mixed|void
	 */
	private $color;

	/**
	 * Kudos_Modal constructor.
     *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->logger = new LoggerService();
		$this->twig = new TwigService();
		$this->color = Settings::get_setting('theme_color');
	    $this->returnUrl = Utils::get_return_url();

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
	 * @param $data
	 * @param bool $echo
	 * @return string|void
	 * @since    1.0.0
	 */
	public function get_donate_modal($data, $echo=false) {

		$privacy_option = Settings::get_setting("privacy_link");
		$privacy_link = __('I agree with the privacy policy.', "kudos-donations");
		if($privacy_option) {
			$privacy_link = sprintf(__('I agree with the %s', "kudos-donations"), '<a target="_blank" href=' . Settings::get_setting("privacy_link") . '>' . __("privacy policy", "kudos-donations") . '</a>.');
		}

		$data = array_merge($data, [
			'color' => $this->color,
			'return_url' => Utils::get_return_url(),
			'nonce' => wp_nonce_field('kudos_submit', '_wpnonce', true, false),
			'privacy_link' => $privacy_link,
			'payment_by' => __('Secure payment by', 'kudos-donations'),

			// Global settings
			'vendor' => Settings::get_setting('payment_vendor'),
			'address' => [
				'enabled' => Settings::get_setting('address_enabled'),
				'required' => Settings::get_setting('address_required')
			]
		]);

		$out = $this->twig->render('/public/modal/donate.modal.html.twig', $data);

		if($echo) {
			echo $out;
		}

		return $out;

	}
}