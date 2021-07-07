<?php

namespace Kudos\Model;

use Kudos\Helpers\Utils;

class ModalModel extends AbstractModel {

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
	public function __construct($html, $id = null) {

		parent::__construct();

		$this->id = $id ?? Utils::generate_id();
		$this->logo_url = Utils::get_logo_url();
		$this->class = 'kudos-donate-modal';
		$this->template = self::TEMPLATE;
		$this->html = $html;

	}

	/**
	 * Returns the modal id.
	 *
	 * @return mixed|string
	 */
	public function get_id() {
		return $this->id;
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