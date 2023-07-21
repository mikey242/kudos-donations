<?php
/**
 * Admin related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Service\AbstractService;

class Admin extends AbstractService {

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_action( 'admin_init', [ $this, 'register_block_editor_assets' ] );
	}

	/**
	 * Register assets for enqueuing in the block editor.
	 */
	public function register_block_editor_assets(): void {
		wp_register_style(
			'kudos-donations-public',
			Assets::get_style( 'admin/kudos-admin-campaigns.jsx.css' ),
			[],
			KUDOS_VERSION
		);
	}
}
