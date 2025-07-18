<?php
/**
 * Abstract Admin Page.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractAdminPage extends AbstractRegistrable implements AdminPageInterface {

	use LoggerAwareTrait;

	private ?string $screen_id;

	/**
	 * {@inheritDoc}
	 */
	public function get_capability(): string {
		return 'manage_options';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'admin_menu';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		// Always return lowest priority for ParentAdminPageInterfaces as these need to be registered first.
		if ( is_a( static::class, ParentAdminPageInterface::class, true ) ) {
			return 0;
		}
		// Use the position as a priority to ensure correct order.
		return static::get_position();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @psalm-suppress InvalidArgument
	 */
	public function register(): void {
		$this->screen_id = null;

		$callback = $this instanceof HasCallbackInterface ? [ $this, 'callback' ] : null;

		if ( $this instanceof ParentAdminPageInterface ) {
			$this->screen_id = add_menu_page(
				$this->get_page_title(),
				$this->get_menu_title(),
				$this->get_capability(),
				$this->get_menu_slug(),
				$callback,
				$this->get_icon_url(),
				$this->get_position()
			);
		} elseif ( $this instanceof SubmenuAdminPageInterface ) {
			$this->screen_id = add_submenu_page(
				$this->get_parent_slug(),
				$this->get_page_title(),
				$this->get_menu_title(),
				$this->get_capability(),
				$this->get_menu_slug(),
				$callback,
				$this->get_position(),
			);
		}

		if ( $this instanceof HasAssetsInterface ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'maybe_register_assets' ] );
		}
	}

	/**
	 * Register the assets if class implements HasAssetsInterface.
	 *
	 * @param string $hook The current admin page.
	 */
	public function maybe_register_assets( string $hook ): void {
		if ( ! $this instanceof HasAssetsInterface ) {
			return;
		}

		if ( $hook === $this->screen_id ) {
			$this->register_assets();
			do_action( "{$this->get_menu_slug()}_page_register_assets" );
		}
	}
}
