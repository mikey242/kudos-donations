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
	 * AdminNotice constructor.
	 *
	 * @param string $type
	 * @param string $notice
	 * @param string|null $extra
	 */
	function __construct( string $type, string $notice, $extra=null ) {

		$this->notice = $notice;
		$this->type = $type;
		$this->extra = $extra;

		add_action( 'admin_notices', [ $this, 'render' ] );

	}

	function render() {
		printf( '<div class="notice notice-%s"><p>%s</p> %s</div>', $this->type , $this->notice, $this->extra );
	}
}