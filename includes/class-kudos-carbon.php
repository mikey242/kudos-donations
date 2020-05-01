<?php

namespace Kudos;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Field;
use Carbon_Fields\Container;
use Carbon_Fields\Block;

class Carbon {

	/**
	 * Carbon constructor.
     *
     * @since      1.0.0
	 */
	public function __construct() {
	    add_action( 'after_setup_theme', [$this, 'boot']);
	    add_action( 'carbon_fields_register_fields', [$this, 'register_fields']);
    }

	/**
     * Boot Carbon Fields
     *
	 * @since      1.0.0
	 */
	public function boot() {
	    Carbon_Fields::boot();
    }

	/**
     * Initiate fields
     *
	 * @since      1.0.0
	 */
	public function register_fields() {
        $this->kudos_options();
        $this->kudos_blocks();
    }

	/**
     * Create the Kudos settings page
     *
	 * @since      1.0.0
	 */
	private function kudos_options() {
        Container::make( 'theme_options', __( 'Kudos', 'kudos-donations' ) )
            ->set_page_menu_title(__('Kudos Settings', 'kudos-donations'))
            ->set_icon('data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>
            '))

	        /*
			 * Mollie tab
			 */
	        ->add_tab(__('Mollie', 'kudos-donations'), [
                Field::make( 'html', 'api_description_html', null )
	                /* translators: %s: Link to Mollie dashboard */
                    ->set_html('<p>' . sprintf(__('You can find your Mollie API keys in your %s. Would you like to test the system first? Then you can use the test API key. To receive payments from your consumers, please use the live API key.', 'kudos-donations'), '<a target="_blank" href="https://mollie.com/dashboard/developers/api-keys">Mollie Dashboard</a>') . '</p>'),
                Field::make('radio', 'kudos_mollie_api_mode', __('Mode', 'kudos-donations'))
	                ->set_help_text( __('Set to "test" to first check the configuration is working correctly. Once you\'re ready to recieve live payments you can switch this to "live"', 'kudos-donations')  )
                    ->add_options([
                        'test' => __('Test', 'kudos-donations'),
                        'live' => __('Live', 'kudos-donations')
                    ])
	                ->set_default_value('test'),
                Field::make( 'text', 'kudos_mollie_test_api_key', __( 'Mollie Test API Key', 'kudos-donations' ) )
	                /* translators: %s: Start of API key */
	                ->set_help_text( sprintf(__('This begins with "%s"', 'kudos-donations'), 'test_')),
                Field::make( 'text', 'kudos_mollie_live_api_key', __( 'Mollie Live API Key', 'kudos-donations' ) )
	                /* translators: %s: Start of API key */
	                ->set_help_text( sprintf(__('This begins with "%s"', 'kudos-donations'), 'live_')),
                Field::make('html', 'check_api_key_button', null)
                    ->set_html('
                        <input id="test_mollie_api_key" type="button" class="button button-secondary" value="Check Api Key">
                        <div id="check_key_spinner" class="spinner"></div>
                        <div id="result_message" class="hidden message"></div>
                    ')
            ] )

	        /*
			 * Button tab
			 */
	        ->add_tab(__('Button', 'kudos-donations'), [
                Field::make('radio_image', 'kudos_button_style', __('Button style', 'kudos-donations'))
                    ->set_help_text( __('Choose the style of the Kudos button', 'kudos-donations')  )
                    ->add_options([
                        'kudos_btn_secondary' => 'data:image/png;base64,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',
                        'kudos_btn_primary' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALUAAAA0CAYAAADMv3nUAAAFYUlEQVR4nO2dQWvbZhiA/UPU4w7JzbBBySE9zbBDbu0pt+7UnpZbb7nluEvTkwuCLIG4IcUeg4QwzyEeKYGGUWNBhsHDTjLjgJk7lzimhmeH73NtOZJjyXGkKa/gAdt6Zb369Eh69VmyYrgN5Qwc/AA/fwc/fQWmIQjBsj4D6W+Vl+WMq7qxa5+cZmFrLvgFEISb2JqD84MbpP7wMvhEBcErhVcuUhdWg09OEPxSWB2S+iwH5oPgExME3zyA09+01J8/QerrECQlCBOS+ga6V8Qop4NPRhBui3KaGLlnwSciCLdF7hkxtueDT0QQbovteWKszwSfiCDcFuszxAJPQhBuGZFaiBwitRA5RGohcojUQuQQqYXIMX2p//3r+gWvnz+NP/3WQ7CS0Cj2p28U1TW1IWhAIXyEV+qNWSilrk87OFivA29AIXyEU+pMAlrV0UL3hp0ngTeiEC7CJ3UmAZ3meEIDlN74zO0FXBw7U0nB0WLgK0fwR7ik9io0QO2dz9ySavqWg9TNuhrXLcH7x4GvJMEb4ZF6Y9Z+MnhXUlsu4zcfQ6UKtMFaCHxFCeMTHqmt196FBqjs+sztBqlNA8w4lEpAHfYdxu8tQ2lX7d3Lq7D3yOE7ElBYgz0DNhPwPgW1YzhLjy5x3i5CQcdWUpBPTBabWYKTZXvehRHzH4zPLI2xnItwopfT6fuO1u6spAuH1DtP/AkNkF+aotQGmE/hErhYGfhsAWq6TGoV1cpu6fe14Xs9n6vpS0noAp2qPb6Vhs1hAXJqXC+2oU+amxPE7u8CRbCK/fiT5+7LbYtv6+UsQqet3peH2/2G9mwAjeQ9krp16k/o1qkqW6YqtQFnTbjsHRHiUKmrejsbt8cdppW4lUFZtNS0wRqqz/O7Kofy0/5nv6w5S/P2hfqexqq/2H09r24RduI3L7NrvF5+6pAVqZ2l/uNHf0J3PqoTS9+5eZDaKgJF9TqjRbJc5n1SUrFf9pJaarcVetFWe+vBDaiVco7NpoFq/xDvJbYn6cmYbdaLt5w2gBW18ZYHywmRuj/e7146+/2EuXmQulyFTk69LhSHpB2iJ/3RkNRll3qyUh84CujY2grsLTqwAp1ezl5ie5JW3WteR6nd4vW8bUckkbo//mjZn9Sd5h3tqeNqhTTX1PvBvfZY36sFcJuPk9SXJfc+9ItjOPQaa/Rr5HHbZ2S8SD1aatOYoKauTr+mzu9iKzfyOa7Xk8MyDPaWeJFab0C2k1I3vMQGLXUCmvdN6rD2fuzoHgvbynihDuuOK2gBGm24HPzbCS9SG7omLzmfzG0+spc9XmKnLvUitIALh3/6yqZVW98rqU0juH7qkkM9ml+B2ohutF6vRSMF2QSYccguQ6MJ1CE/KJlHqXsbRrcK1hJk4qp/O5+EVvt61+K4sVOX2tDnG22orKhc3i7AURq6bdWFee+k3piFhuVd6kl/UXQaum1oHcLhiJ/Is8l+X3NvaOUc9ppepdaylovqKPFlaEIt6XCCOmbsXUhtxsE6tufS0ZcaVOr3UGrTUNdOdz56MJoJpBaiSrikNg19UZMHsX1fpSdElfBJbRr6euoxe0Qm6tYTokg4pTYNfefLm9FCy50vggPTl/qfP6F7ZeeqMf70Ww+VvLV3fZkru7fwi6IQVeRuciFyiNRC5BCphcghUguRQ6QWIodILUQOeZKAEC3WZ+SZL0LE2J6Xp3MJESP3TJ6jKESMcpoY3Sv1pNCgkxGESdme10+8BTj9NfiEBGFS/v4dQEsNUHC4t0wQ/i8UVr+o3JcaoPAq+OQEwSsfXto0tksNcL4PW3PBJyoIN7E1B+cH1xT+D8sT4LVVufYDAAAAAElFTkSuQmCC',
                    ])
                    ->set_default_value('kudos_btn-primary'),
                Field::make('html', 'shortcode_instructions', null)
	                ->set_html('
						<h4>Buttons can be placed using either a Gutenberg block or a shortcode</h4>
					'),
            ])

	        /*
	         * Completed payment tab
	         */
            ->add_tab(__('Completed Payment', 'kudos-donations'), [
                    Field::make('checkbox', 'kudos_custom_return_enable', __('Use custom return URL', 'kudos-donations'))
	                    ->set_help_text( 'Useful if you want to create a custom thank you page' )
	                    ->set_default_value(false),
                    Field::make('text', 'kudos_custom_return_url', __('URL', 'kudos-donations'))
	                    ->set_help_text( 'e.g: https://mijnwebsite.nl/bedankt' )
                        ->set_conditional_logic([
                                [
                                    'field' => 'kudos_custom_return_enable',
                                    'value' => true
                                ]
                        ]),
                    Field::make('checkbox', 'kudos_return_message_enable', __('Show pop-up message when payment complete', 'kudos-donations'))
	                    ->set_help_text( __('Enable this to show a pop-up thanking the customer for their donation.', 'kudos-donations')  )
                        ->set_default_value(true),
	                Field::make('text', 'kudos_return_message_header', __('Message header', 'kudos-donations'))
		                ->set_default_value(__('Thanks!', 'kudos-donations'))
		                ->set_conditional_logic([
			                [
				                'field' => 'kudos_return_message_enable',
				                'value' => true
			                ]
		                ]),
	                Field::make('textarea', 'kudos_return_message_text', __('Message text', 'kudos-donations'))
		                /* translators: %s: Numerical currency value */
		                ->set_default_value(sprintf(__('Thanks a lot for your donation of € %s. We greatly appreciate your support. Thanks to your efforts, culture remains accessible to everyone.', 'kudos-donations'), '{{value}}'))
		                ->set_help_text(sprintf(__('You can use the following variables %s', 'kudos-donations'), '{{value}}, {{name}}'))
		                ->set_conditional_logic([
			                [
				                'field' => 'kudos_return_message_enable',
				                'value' => true
			                ]
		                ]),
            ])
        ;
    }

	/**
     * Add Kudos blocks
     *
	 * @since      1.0.0
	 */
	public function kudos_blocks() {
		/* translators: %s: Name of this plugin */
        Block::make( sprintf(__('%s Button', 'kudos-donations'), 'Kudos'))
            ->set_icon('heart')
            ->add_fields([
                Field::make('text', 'kudos_button_label', __('Button label', 'kudos-donations'))
	                ->set_default_value(__('Donate now', 'kudos-donations')),
	            Field::make('text', 'kudos_modal_header', __('Modal header', 'kudos-donations'))
	                 ->set_default_value(__('Support us!', 'kudos-donations')),
                Field::make('text', 'kudos_modal_text', __('Modal text', 'kudos-donations'))
                    ->set_default_value(__("We really appreciate that you want to help us out.", 'kudos-donations')),
            ])
            ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
                $label = $fields['kudos_button_label'];
                $text = $fields['kudos_modal_text'];
                $header = $fields['kudos_modal_header'];
                $classes = $attributes ? $attributes['className'] : '';

                    echo "<div class='kudos_block_button $classes'>";
                        kudos_button($label, $header, $text);
                    echo "</div><!-- /.block -->";

            } );
    }
}

new Carbon();