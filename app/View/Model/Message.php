<?php

namespace Kudos\View\Model;

class Message extends AbstractModel {

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
	public function __construct($title = '', $body = '') {

		$this->header_text = $title;
		$this->body_text = $body;

		parent::__construct();

	}

	public function set_title($title) {
		$this->header_text = $title;
	}

	public function set_body($body) {
		$this->body_text = $body;
	}
}