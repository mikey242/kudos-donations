<?php

use Kudos\Kudos_Public;

class Kudos_Modal {

	/**
	 * @var bool|mixed|void
	 */
	private $header;
	/**
	 * @var bool|mixed|void
	 */
	private $text;
	/**
	 * @var bool
	 */
	private $ready;

	/**
	 * Kudos_Modal constructor.
     *
	 * @since      1.0.0
	 */
	public function __construct() {

        $this->header = get_option('_kudos_form_header');
	    $this->text = get_option('_kudos_form_text');
	    $this->ready = Kudos_Public::ready();
    }

	/**
	 * The actual modal markup
	 *
	 * @since    1.0.0
	 */
	function get_modal() {

		if($this->ready) {
			?>
                <div id="kudos_form_modal" class="kudos_modal" aria-hidden="true">
                    <div class="kudos_modal_overlay" tabindex="-1" data-micromodal-close>
                        <div class="kudos_modal_container" role="dialog" aria-modal="true" aria-labelledby="kudos_modal-title">
                            <header class="kudos_modal_header">
                                <div class="kudos_modal_logo"></div>
                                <button class="kudos_modal_close" aria-hidden="true" aria-label="Close modal" data-micromodal-close></button>
                            </header>
                            <div id="kudos_modal_content" class="kudos_modal_content mt-4">
                                <div class="text-center">
                                    <h2 id="kudos_modal_title" class="font-normal"><?php echo $this->header ?></h2>
                                    <p id="kudos_modal_text"><?php echo $this->text ?></p>
                                    <p><small class="kudos_error_message error"></small></p>
                                </div>
                                <form id="kudos_form" action="">
                                    <input type="text" name="name" placeholder="<?php _e('Name', 'kudos-donations')?>" />
                                    <input type="email" name="email_address" placeholder="<?php _e('E-mail address', 'kudos-donations')?>" />
                                    <?php /* translators: %s: Star denoting required field */ ?>
                                    <input required type="text" min="1" name="value" placeholder="<?php printf(__('Amount %s', 'kudos-donations'), '*') ?>" />
                                    <div class="payment_by mollie">
                                        <small class="text-gray-600">
                                            <?php _e('Secure payment by', 'kudos-donations') ?>
                                        </small>
                                    </div>
                                    <footer class="kudos_modal_footer text-center">
                                        <button class="kudos_btn kudos_btn_primary_outline mr-3" type="button" data-micromodal-close aria-label="<?php _e('Cancel', 'kudos-donations') ?>"><?php _e('Cancel', 'kudos-donations') ?></button>
                                        <button id="kudos_submit" class="kudos_btn kudos_btn_primary" type="submit" aria-label="<?php _e('Donate', 'kudos-donations') ?>"><?php _e('Donate', 'kudos-donations') ?></button>
                                    </footer>
                                </form>
                            </div>
                            <i class="kudos_spinner"></i>
                        </div>
                    </div>
                </div>
			<?php
		}
	}
}