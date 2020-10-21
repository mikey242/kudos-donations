<?php

/*
 * @source https://wordpress.stackexchange.com/questions/224485/how-to-pass-parameters-to-admin-notices
 */

namespace Kudos\Service;

class AdminNotice {

	/**
	 * @var string
	 */
	private $type;
	/**
	 * @var string
	 */
	private $notice;
	/**
	 * @var string|null
	 */
	private $extra;
	/**
	 * @var bool|null
	 */
	private $isDismissible;

	/**
	 * AdminNotice constructor.
	 *
	 * @param string $type
	 * @param string $notice
	 * @param string|null $extra
	 * @param bool $isDismissible
	 * @since 2.0.0
	 */
	function __construct( string $notice, string $type = 'success', $extra = null, $isDismissible = true ) {

		$this->notice        = $notice;
		$this->type          = $type;
		$this->extra         = $extra;
		$this->isDismissible = $isDismissible;

		add_action( 'admin_notices', [ $this, 'render' ] );

	}

	/**
	 * Outputs the notice
	 *
	 * @since 2.0.0
	 */
	function render() {

		printf( '
			<div class="notice notice-%s %s"><p>%s</p>%s</div>',
			$this->type,
			$this->isDismissible ? 'is-dismissible' : null,
			$this->notice,
			$this->extra
		);

	}
}