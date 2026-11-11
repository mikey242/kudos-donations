<?php
/**
 * Parent Admin Page.
 *
 * @link https://code.iseard.media/michael/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

class DonationsAdminPage extends AbstractAdminPage implements ParentAdminPageInterface {

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Donations', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return apply_filters( 'kudos_admin_top_page', 'kudos-campaigns' );
	}

	/**
	 * Determine position of Kudos in main menu.
	 *
	 * @see https://wordpress.stackexchange.com/questions/8779/placing-a-custom-post-type-menu-above-the-posts-menu-using-menu-position
	 */
	public static function get_position(): int {
		return 75;
	}

	/**
	 * Kudos logo for the admin menu. Only its shape is used, see print_menu_icon_style().
	 */
	private const MENU_ICON_SVG = '<svg viewBox="0 0 555 449" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M0 65.107a65.114 65.114 0 0 1 19.07-46.04A65.114 65.114 0 0 1 65.11-.003h.002c36.09 0 65.346 29.256 65.346 65.346v317.713a65.292 65.292 0 0 1-19.125 46.171 65.292 65.292 0 0 1-46.171 19.125h-.001c-35.987 0-65.16-29.173-65.16-65.16L0 65.107ZM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781Z"/></svg>';

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		parent::register();
		add_action( 'admin_head', [ $this, 'print_menu_icon_style' ] );
	}

	/**
	 * No icon image: the icon is drawn with CSS instead, see print_menu_icon_style().
	 */
	public function get_icon_url(): string {
		return 'none';
	}

	/**
	 * Draws the menu icon as a CSS mask filled with the text colour WordPress gives menu
	 * icons. It then has the right colour for every state and admin colour scheme straight
	 * away. An SVG image as icon URL is recoloured by JavaScript once the page has loaded,
	 * so it showed in its own colour until then.
	 */
	public function print_menu_icon_style(): void {
		$menu_id = preg_replace( '|[^a-zA-Z0-9_:.]|', '-', get_plugin_page_hookname( static::get_menu_slug(), '' ) );
		//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$mask = 'url("data:image/svg+xml;base64,' . base64_encode( self::MENU_ICON_SVG ) . '") no-repeat center / 20px auto';

		// Not escaped: entities are not decoded inside <style>. The id is limited to the
		// characters above and the mask to a constant's base64, so neither can end the rule.
		printf(
			'<style id="kudos-menu-icon">#adminmenu #%1$s div.wp-menu-image::before{content:"";background-color:currentColor;-webkit-mask:%2$s;mask:%2$s}</style>',
			$menu_id, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$mask // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}
