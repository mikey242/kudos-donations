<?php

namespace Kudos\Front;

use Kudos\Helpers\Utils;

class KudosModal extends AbstractRender {

	const MESSAGE_TEMPLATE = '/public/modal/message.modal.html.twig';
	const DONATE_TEMPLATE = '/public/modal/donate.modal.html.twig';

	/**
	 * @var string
	 */
	protected $modal_title;
	/**
	 * @var string
	 */
	protected $modal_text;
	/**
	 * @var string|null
	 */
	protected $content;
	/**
	 * @var string|null
	 */
	protected $spinner;
	/**
	 * @var string|null
	 */
	protected $logo_url;
	/**
	 * @var string
	 */
	protected $class;

	/**
	 * KudosModal constructor.
	 *
	 * @param string|null $id The id to use for the modal.
	 */
	public function __construct( string $id = null ) {

		parent::__construct($id);
		
		$this->template = self::MESSAGE_TEMPLATE;
		$this->logo_url = Utils::get_logo_url();

	}

	/**
	 * Get message modal markup.
	 *
	 * @param string $title
	 * @param string|null $message
	 */
	public function create_message_modal( string $title, string $message = null ) {

		$this->template = self::MESSAGE_TEMPLATE;
		$this->class = 'kudos-message-modal';

		// Template specific atts
		$this->modal_title = $title ?? '';
		$this->modal_text = $message ?? '';

	}

	/**
	 * Get the donate modal markup.
	 *
	 * @param KudosForm $form
	 */
	public function create_donate_modal( KudosForm $form ) {

		$this->template = self::DONATE_TEMPLATE;
		$this->class = 'kudos-donate-modal';

		// Template specific atts.
		$this->content = $form;
		$this->spinner = Utils::get_kudos_logo_markup( 'black', 30 );

	}
}