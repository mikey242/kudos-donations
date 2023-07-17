<?php
/**
 * Abstract Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Infrastructure\Delayed;
use IseardMedia\Kudos\Infrastructure\HasAssetsInterface;
use IseardMedia\Kudos\Infrastructure\HasCallbackInterface;
use IseardMedia\Kudos\Infrastructure\Registrable;

abstract class AbstractAdminPage implements AdminPageInterface, Registrable, Delayed {

	use TableColumnsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {

		$suffix = add_submenu_page(
			$this->get_parent_slug(),
			$this->get_page_title(),
			$this->get_menu_title(),
			$this->get_capability(),
			$this->get_menu_slug(),
			$this instanceof HasCallbackInterface ? [ $this, 'callback' ] : '',
			$this->get_position(),
		);

		if ( $this instanceof HasAssetsInterface ) {
			add_action(
				"load-$suffix",
				function (): void {
					add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
				}
			);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return 'kudos-campaigns';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_capability(): string {
		return 'manage_options';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 10;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_menu' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool {
		return true;
	}

	/**
	 * Change menu position.
	 */
	protected function get_position(): ?int {
		return null;
	}
}
