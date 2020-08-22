<?php

namespace Kudos\Front;

use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\TwigService;

class KudosButton {

	/**
	 * @var TwigService
	 */
	private $twig;
	/**
	 * @var mixed|string
	 */
	private $alignment;
	/**
	 * @var mixed
	 */
	private $target;
	/**
	 * @var bool|mixed|void
	 */
	private $label;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var false|mixed|void
	 */
	private $color;
	/**
	 * @var LoggerService
	 */
	private $logger;
	/**
	 * @var string
	 */
	private $header;
	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var string
	 */
	private $amount_type;
	/**
	 * @var string
	 */
	private $fixed_amounts;

	/**
	 * KudosButton constructor.
	 *
	 * @since    1.0.0
	 * @param array $atts
	 */
	public function __construct($atts) {

		$this->twig = new TwigService();
		$this->logger = new LoggerService();
		$this->header = $atts['modal_header'];
		$this->text = $atts['welcome_text'];
		$this->color = Settings::get_setting('theme_color');
		$this->label = $atts['button_label'];
		$this->alignment = $atts['alignment'];
		$this->amount_type = $atts['amount_type'];
		$this->fixed_amounts = $atts['fixed_amounts'];
		$this->id = uniqid('kudos_modal-');

	}

	/**
	 * Get the button markup
	 *
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_button($echo=true) {

		$data = [
			'color' => Settings::get_setting('theme_color'),
			'alignment' => $this->alignment,
			'label' => $this->label,
			'target' => $this->id
		];

		$out = $this->twig->render('public/kudos.button.html.twig', $data);

		if($echo) {
			echo $out;
		}

		return $out;
	}

	/**
	 * Get the donate modal markup
	 *
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_donate_modal($echo=true) {

		$modal = new KudosModal();

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

