<?php
/**
 * Creates an admin notice.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin\Notice;

/**
 * Class AdminNotice
 */
class AdminNotice {

	public const ERROR   = 'notice-error';
	public const WARNING = 'notice-warning';
	public const SUCCESS = 'notice-success';
	public const INFO    = 'notice-info';

	/**
	 * Whether this notice is dismissible or not.
	 * Use AdminDismissibleNotice for dismissible notices.
	 *
	 * @var bool
	 */
	protected bool $is_dismissible = false;
	/**
	 * The style the message will be displayed in.
	 *
	 * @var string
	 */
	private string $level;
	/**
	 * The message to be displayed.
	 *
	 * @var string
	 */
	private string $message;

	/**
	 * Error notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function error( string $message ): void {
		$this->message( self::ERROR, $message );
	}

	/**
	 * Warning notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function warning( string $message ): void {
		$this->message( self::WARNING, $message );
	}

	/**
	 * Success notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function success( string $message ): void {
		$this->message( self::SUCCESS, $message );
	}

	/**
	 * Info notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function info( string $message ): void {
		$this->message( self::INFO, $message );
	}

	/**
	 * Sets up the message and adds the hook.
	 *
	 * @param string $level The message level.
	 * @param string $message The message content.
	 */
	public function message( string $level, string $message ): void {
		$this->level   = $level;
		$this->message = $message;
		$this->hook();
	}

	/**
	 * Add the required hook to display the notice.
	 */
	public function hook(): void {
		add_action( 'admin_notices', [ $this, 'render' ] );
	}

	/**
	 * Generates the notice markup.
	 */
	public function render(): void {
		printf(
			'<div class="notice %s %s"><p>%s</p></div>',
			esc_attr( $this->level ),
			esc_html( $this->is_dismissible ? 'is-dismissible' : '' ),
			wp_kses(
				$this->message,
				[
					'form'   => [
						'method' => [],
					],
					'input'  => [
						'id'    => [],
						'type'  => [],
						'name'  => [],
						'value' => [],
					],
					'button' => [
						'class' => [],
						'name'  => [],
						'type'  => [],
						'value' => [],
					],
				]
			)
		);
	}
}
