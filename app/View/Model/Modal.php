<?php

namespace Kudos\View\Model;

use Kudos\Helpers\Utils;

class Modal extends AbstractModel {

	const TEMPLATE = 'public/modal/modal.html.twig';

	/**
	 * @var string|null
	 */
	protected $logo_url;
	/**
	 * @var string
	 */
	protected $html;
	/**
	 * @var mixed|string
	 */
	protected $id;
	/**
	 * Class to apply to Modal.
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * Modal constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->logo_url = Utils::get_logo_url();
		$this->class = 'kudos-donate-modal';

	}

	/**
	 * Set the content of the modal.
	 *
	 * @param string $html
	 */
	public function set_content(string $html) {
		$this->html = $html;
	}

	/**
	 * Set the class property.
	 *
	 * @param $class
	 */
	public function set_class($class) {

		$this->class = $class;

	}

}