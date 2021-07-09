<?php

namespace Kudos\Controller;

use Kudos\Service\TwigService;

class MessageController extends Controller {

	const TEMPLATE = 'public/modal/embeds/_message.html.twig';

	/**
	 * @var string The text to display in header element.
	 */
	protected $header_text;
	/**
	 * @var string The text to display in body element.
	 */
	protected $body_text;

	/**
	 * Creates a message modal.
	 */
	public function __construct(TwigService $twig_service) {

		parent::__construct($twig_service);

	}

	public function set_title($title) {
		$this->header_text = $title;
	}

	public function set_body($body) {
		$this->body_text = $body;
	}
}