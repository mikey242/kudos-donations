<?php
/**
 * A single admin notice.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Notice;

/**
 * Immutable notice value object. Carries only data so it can cross the REST boundary
 * unchanged; whether/when it appears is decided by its source or store, not by the notice,
 * and how it renders (WordPress CSS class, `@wordpress/notices` status) is the consumer's job.
 */
final class Notice {

	public const INFO    = 'info';
	public const SUCCESS = 'success';
	public const WARNING = 'warning';
	public const ERROR   = 'error';

	/**
	 * Where a notice is allowed to appear.
	 *
	 * BOTH  — the in-app React notices (REST) and native admin_notices screens.
	 * APP   — the React app only (e.g. it duplicates something a foreign admin screen shows).
	 * ADMIN — native admin_notices only (e.g. the React app already has its own UI for it).
	 */
	public const BOTH  = 'both';
	public const APP   = 'app';
	public const ADMIN = 'admin';

	public string $id;
	public string $message;
	public string $level;
	public bool $dismissible;
	public string $context;
	public bool $logo;

	/**
	 * @param string $id          Stable key used for de-duplication and dismissal.
	 * @param string $message     The (HTML) message body.
	 * @param string $level       One of the level constants.
	 * @param bool   $dismissible Whether the user can dismiss it (which removes it from the store).
	 * @param string $context     Which channel(s) may show it: one of BOTH, APP, ADMIN.
	 * @param bool   $logo        Whether to show the logo or not.
	 */
	public function __construct( string $id, string $message, string $level = self::INFO, bool $dismissible = false, string $context = self::BOTH, bool $logo = true ) {
		$this->id          = $id;
		$this->message     = $message;
		$this->level       = $level;
		$this->dismissible = $dismissible;
		$this->context     = $context;
		$this->logo        = $logo;
	}
}
