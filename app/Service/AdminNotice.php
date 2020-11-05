<?php

/*
 * @source https://wordpress.stackexchange.com/questions/224485/how-to-pass-parameters-to-admin-notices
 */

namespace Kudos\Service;

class AdminNotice {

	/**
	 * Notice type (success, warning etc.).
	 *
	 * @var string
	 */
	private $type;
	/**
	 * Message to be displayed.
	 *
	 * @var string
	 */
	private $notice;
	/**
	 * Extra content for after message.
	 *
	 * @var string|null
	 */
	private $extra;
	/**
	 * Whether the notice can be dismissed or not.
	 *
	 * @var bool|null
	 */
	private $is_dismissible;

	/**
	 * AdminNotice constructor.
	 *
	 * @param string      $notice Message to be displayed.
	 * @param string      $type Notice type (success, warning etc.).
	 * @param string|null $extra Extra content for after message.
	 * @param bool        $is_dismissible Whether the notice can be dismissed or not.
	 * @since 2.0.0
	 */
	public function __construct( string $notice, string $type = 'success', $extra = null, $is_dismissible = true ) {

		$this->notice         = $notice;
		$this->type           = $type;
		$this->extra          = $extra;
		$this->is_dismissible = $is_dismissible;

		add_action( 'admin_notices', [ $this, 'render' ] );

	}

	/**
	 * Outputs the notice
	 *
	 * @since 2.0.0
	 */
	public function render() {

		printf(
			'<div class="notice notice-%s %s"><p>%s</p>%s</div>',
			$this->type,
			$this->is_dismissible ? 'is-dismissible' : null,
			$this->notice,
			$this->extra
		);

	}
}
