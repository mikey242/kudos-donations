<?php

namespace Kudos\Controller;

use Kudos\Helpers\Utils;
use Kudos\Service\TwigService;

class ModalController extends Controller {

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
	public function __construct(TwigService $twig_service) {

		parent::__construct($twig_service);

		$this->logo_url = Utils::get_logo_url();
		$this->class = 'kudos-donate-modal';

	}

	public function set_content($html) {
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