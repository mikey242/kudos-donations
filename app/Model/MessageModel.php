<?php

namespace Kudos\Model;

class MessageModel extends AbstractModel {

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
	 *
	 * @param $title
	 * @param $body
	 */
	public function __construct($title, $body) {

		parent::__construct();

		$this->template    = self::TEMPLATE;
		$this->header_text = $title;
		$this->body_text   = $body;

	}
}