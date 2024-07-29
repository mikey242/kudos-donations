<?php
/**
 * Abstract Content Type. Used as a base for creating Custom Post Types and Custom Taxonomies.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

use IseardMedia\Kudos\Container\Delayed;
use IseardMedia\Kudos\Container\Registrable;

/**
 * AbstractContentType class.
 */
abstract class AbstractContentType implements ContentTypeInterface, Registrable, Delayed {

	use LabelsTrait;
	use RegisterMetaTrait;
	use RegisterRestFieldsTrait;

	/**
	 * Whether to show the content in the REST API.
	 */
	protected const SHOW_IN_REST = true;
	/**
	 * Whether the content is intended for use publicly.
	 */
	protected const PUBLIC = false;
	/**
	 * Whether the content should be visible in the Admin UI.
	 */
	protected const SHOW_UI = true;
	/**
	 * Should a link to the UI be added to the menu.
	 * Since Kudos Donations uses its own menu we will add this ourselves later.
	 */
	protected const SHOW_IN_MENU = false;
	/**
	 * Whether the content is hierarchical.
	 */
	protected const HIERARCHICAL = false;

	/**
	 * {@inheritDoc}
	 */
	public function get_args(): array {
		return [
			'description'  => $this->get_description(),
			'labels'       => $this->get_labels(),
			'capabilities' => $this->get_capabilities(),
			'public'       => static::PUBLIC,
			'show_ui'      => static::SHOW_UI,
			'show_in_menu' => static::SHOW_IN_MENU,
			'show_in_rest' => static::SHOW_IN_REST,
			'hierarchical' => static::HIERARCHICAL,
			'map_meta_cap' => true,
		];
	}

	/**
	 * Gets the capabilities for the current content type.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_post_type_capabilities/
	 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
	 */
	abstract protected function get_capabilities(): array;

	/**
	 * Returns the default labels.
	 *
	 * @param string $singular_uc Singular uppercase name.
	 * @param string $singular_lc Singular lowercase name.
	 * @param string $plural_uc Plural uppercase name.
	 * @param string $plural_lc Plural lowercase name.
	 */
	protected function get_default_labels( string $singular_uc, string $singular_lc, string $plural_uc, string $plural_lc ): array {
		return [
			'name'                     => $plural_uc,
			'singular_name'            => $singular_uc,
			'add_new'                  => _x( 'Add New', 'Menu label', 'kudos-donations' ),
			/* translators: %s: Custom post type singular name. */
			'add_new_item'             => sprintf( _x( 'Add New %s', 'Label for adding a new singular item', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'edit_item'                => sprintf( _x( 'Edit %s', 'Label for editing a singular item', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'new_item'                 => sprintf( _x( 'New %s', 'Label for the new item page title', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'view_item'                => sprintf( _x( 'View %s Name', 'Label for viewing a singular item', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'view_items'               => sprintf( _x( 'View %s Name', 'Label for viewing post type archives', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type plural name. */
			'search_items'             => sprintf( _x( 'Search %s', 'Label for searching plural items', 'kudos-donations' ), $plural_uc ),
			/* translators: %s: Custom post type plural name. */
			'not_found'                => sprintf( _x( 'No %s found.', 'Label used when no items are found', 'kudos-donations' ), $plural_lc ),
			/* translators: %s: Custom post type plural name. */
			'not_found_in_trash'       => sprintf( _x( 'No %s found.', 'Label used when no items are in the Trash', 'kudos-donations' ), $plural_lc ),
			/* translators: %s: Custom post type singular name. */
			'parent_item_colon'        => sprintf( _x( 'Parent %s', 'Label used to prefix parents of hierarchical items', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type plural name. */
			'all_items'                => sprintf( _x( 'All %s', 'Label to signify all items in a submenu link', 'kudos-donations' ), $plural_uc ),
			/* translators: %s: Custom post type singular name. */
			'archives'                 => sprintf( _x( '%s Archives', 'Label for archives in nav menus', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'attributes'               => sprintf( _x( '%s Attributes', 'Label for the attributes meta box', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'insert_into_item'         => sprintf( _x( 'Add to %s', 'Label for the media frame button', 'kudos-donations' ), $singular_lc ),
			/* translators: %s: Custom post type singular name. */
			'uploaded_to_this_item'    => sprintf( _x( 'Uploaded to this %s', 'Label for the media frame filter', 'kudos-donations' ), $singular_lc ),
			/* translators: %s: Custom post type singular name. */
			'featured_image'           => sprintf( _x( '%s Featured image', 'Label for setting the featured image', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'set_featured_image'       => sprintf( _x( '%s Featured image', 'Label for setting the featured image', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'remove_featured_image'    => sprintf( _x( 'Remove %s featured image', 'Label for removing the featured image', 'kudos-donations' ), $singular_lc ),
			/* translators: %s: Custom post type singular name. */
			'use_featured_image'       => sprintf( _x( 'Use as %s featured image', 'Label in the media frame for using a featured image', 'kudos-donations' ), $singular_lc ),
			/* translators: %s: Custom post type singular name. */
			'filter_items_list'        => sprintf( _x( 'Filter %s list', 'Label for the table views hidden heading', 'kudos-donations' ), $singular_lc ),
			/* translators: %s: Custom post type plural name. */
			'filter_by_date'           => sprintf( _x( 'Filter %s by date', 'Label for the date filter in list tables', 'kudos-donations' ), $plural_lc ),
			/* translators: %s: Custom post type singular name. */
			'items_list_navigation'    => sprintf( _x( '%s list navigation', 'Label for the table pagination hidden heading', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'items_list'               => sprintf( _x( '%s list', 'Label for the table hidden heading', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_published'           => sprintf( _x( '%s published', 'Label used when an item is published', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_published_privately' => sprintf( _x( '%s published privately', 'Label used when an item is published with private visibility', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_reverted_to_draft'   => sprintf( _x( '%s reverted to draft', 'Label used when an item is switched to a draft', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_scheduled'           => sprintf( _x( '%s scheduled', 'Label used when an item is scheduled for publishing', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_updated'             => sprintf( _x( '%s updated', 'Label used when an item is updated', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_link'                => sprintf( _x( '%s link', 'Title for a navigation link block variation', 'kudos-donations' ), $singular_uc ),
			/* translators: %s: Custom post type singular name. */
			'item_link_description'    => sprintf( _x( 'A link to a %s', 'Description for a navigation link block variation.', 'kudos-donations' ), $singular_lc ),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 5;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool {
		return true;
	}

	/**
	 * Returns a formatted id based on the post id and date of post.
	 *
	 * @param int $post_id WordPress post id.
	 */
	public static function get_formatted_id( int $post_id ): string {
		$transaction = get_post( $post_id );
		$date        = $transaction->post_date;
		$year        = substr( $date, 0, 4 );
		$type        = substr( static::get_slug(), 6, 2 );

		return 'k' . $type . '_' . $year . $post_id;
	}
}
