<?php

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Infrastructure\Container\Delayed;
use IseardMedia\Kudos\Infrastructure\Container\Registrable;

abstract class AbstractAdminPage implements AdminPageInterface, Registrable, Delayed
{
	public function register(): void {
		$suffix = add_submenu_page(
			$this->get_parent_slug(),
			$this->get_page_title(),
			$this->get_menu_title(),
			$this->get_capability(),
			$this->get_menu_slug(),
			[$this, 'callback']
		);
		add_action("load-$suffix", function () {
			add_action("admin_enqueue_scripts", [$this, 'register_assets']);
		});
	}

	public function get_parent_slug(): string {
		return 'kudos-campaigns';
	}

	public function get_capability(): string {
		return 'manage_options';
	}

	public static function get_registration_action_priority(): int {
		return 10;
	}

	public static function get_registration_actions(): array {
		return ['admin_menu'];
	}

	public function is_enabled(): bool {
		return true;
	}
}
