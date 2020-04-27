<?php

namespace Kudos;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Field;
use Carbon_Fields\Container;
use Carbon_Fields\Block;

class Carbon {

    public function __construct() {
	    add_action( 'after_setup_theme', [$this, 'boot']);
	    add_action( 'carbon_fields_register_fields', [$this, 'register_fields']);
    }

    public function boot() {
	    Carbon_Fields::boot();
    }

    public function register_fields() {
        $this->kudos_options();
        $this->kudos_blocks();
    }

    private function kudos_options() {
        Container::make( 'theme_options', __( 'Kudos', 'kudos' ) )
            ->set_page_menu_title('Kudos Settings')
            ->set_icon('data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>
            '))
            ->add_fields( [
                Field::make( 'separator', 'crb_separator', __( 'Mollie', 'kudos' ) ),
                Field::make( 'html', 'api_description_html', __('Html', 'kudos') )
                    ->set_html('You can find your Mollie API keys in your <a target="_blank" href="https://mollie.com/dashboard/developers/api-keys">Mollie Dashboard</a>. Would you like to test the system first? Then you can use the test API key. To receive payments from your consumers, please use the live API key.'),
                Field::make('radio', 'kudos_mollie_api_mode', __('Mode', 'kudos'))
                    ->add_options([
                        'test' => 'Test',
                        'live' => 'Live'
                    ]),
                Field::make( 'text', 'kudos_mollie_test_api_key', __( 'Mollie Test API Key', 'kudos' ) ),
                Field::make( 'text', 'kudos_mollie_live_api_key', __( 'Mollie Live API Key', 'kudos' ) ),
                Field::make('html', 'check_api_key_button', null)
                    ->set_html('
                        <input id="test_mollie_api_key" type="button" class="button button-secondary" value="Check Api Key">
                        <div id="check_key_spinner" class="spinner"></div>
                        <div id="result_message" class="hidden message"></div>
                    '),
            ] )
        ;
    }

    public function kudos_blocks() {
        Block::make(__('Kudos Button', 'kudos'))
            ->set_icon('heart')
            ->add_fields([
                Field::make('text', 'button_label', __('Button Label', 'kudos'))
            ])
            ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
                $label = $fields['button_label'];
                ?>
                    <div class="kudos_block_button">
                <?php
                    kudos_button();
                ?>
                    </div><!-- /.block -->
                <?php
            } );
    }
}

new Carbon();