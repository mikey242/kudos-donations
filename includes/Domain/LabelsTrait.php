<?php
/**
 * LabelsTrait.
 *
 * This trait generates the labels needed for the post or taxonomy
 * during registration. It uses the slug to automatically 'guess' the
 * singular and plural names.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

/**
 * LabelsTrait class.
 */
trait LabelsTrait {

	/**
	 * Slug needed to generate default labels.
	 */
	abstract public static function get_slug(): string;

	/**
	 * Defines the default labels for the object type.
	 *
	 * @param string $singular_uc Singular upper-case term.
	 * @param string $singular_lc Singular lower-case term.
	 * @param string $plural_uc Plural upper-case term.
	 * @param string $plural_lc Plural lower-case term.
	 * @return array
	 */
	abstract protected function get_default_labels( string $singular_uc, string $singular_lc, string $plural_uc, string $plural_lc ): array;

	/**
	 * The singular name.
	 */
	protected function get_singular_name(): string {
		return str_replace( '-', ' ', $this->get_slug() );
	}

	/**
	 * The plural name.
	 */
	protected function get_plural_name(): string {
		return $this->get_singular_name();
	}

	/**
	 * Allow overriding individual generated labels.
	 *
	 * @return array
	 */
	protected function get_custom_labels(): array {
		return [];
	}

	/**
	 * Returns the labels for the child class to be used in registration.
	 *
	 * @return array Array of label names and values.
	 */
	private function get_labels(): array {
		$singular    = $this->get_singular_name();
		$plural      = $this->get_plural_name();
		$singular_uc = ucfirst( $singular );
		$singular_lc = lcfirst( $singular );
		$plural_uc   = ucfirst( $plural );
		$plural_lc   = lcfirst( $plural );

		return array_merge(
			$this->get_default_labels( $singular_uc, $singular_lc, $plural_uc, $plural_lc ),
			$this->get_custom_labels()
		);
	}
}
