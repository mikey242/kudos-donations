<?php
/**
 * Field Types.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Enum;

class FieldType {

	public const STRING  = 'string';
	public const BOOLEAN = 'boolean';
	public const INTEGER = 'integer';
	public const FLOAT   = 'float';
	public const NUMBER  = 'number';
	public const ARRAY   = 'array';
	public const EMAIL   = 'email';
	public const URL     = 'url';
	public const IMAGE   = 'img';
	public const OBJECT  = 'object';
}
