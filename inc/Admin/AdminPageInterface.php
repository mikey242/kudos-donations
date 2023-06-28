<?php

namespace IseardMedia\Kudos\Admin;

interface AdminPageInterface {

	/**
	 * Get the page title.
	 *
	 * @return string
	 */
	public function get_page_title(): string;

	/**
	 * Get the menu title.
	 *
	 * @return string
	 */
	public function get_menu_title(): string;

	/**
	 * Get the capability for access.
	 *
	 * @return string
	 */
	public function get_capability(): string;

	/**
	 * Get the menu slug for this page.
	 *
	 * @return string
	 */
	public function get_menu_slug(): string;

	/**
	 * Returns the slug for the parent page.
	 *
	 * @return string
	 */
	public function get_parent_slug(): string;

	/**
	 * The function to be called to output the content for this page.
	 *
	 * @return void
	 */
	public function callback(): void;

	/**
	 * Callback used for registering page assets.
	 *
	 * @return void
	 */
	public function register_assets(): void;

}