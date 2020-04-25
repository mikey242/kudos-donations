<?php

//namespace Kudos;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Field;
use Carbon_Fields\Container;
use Carbon_Fields\Block;

add_action( 'after_setup_theme', 'crb_kudos_load' );
function crb_kudos_load() {
	Carbon_Fields::boot();
}

add_action( 'carbon_fields_register_fields', 'crb_kudos_attach_theme_options' );
function crb_kudos_attach_theme_options() {
	Container::make( 'theme_options', __( 'Kudos', 'kudos' ) )
       ->set_page_menu_title('Kudos Settings')
       ->set_icon('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512"><path fill="#f0f5fa99" d="M256 416c114.9 0 208-93.1 208-208S370.9 0 256 0 48 93.1 48 208s93.1 208 208 208zM233.8 97.4V80.6c0-9.2 7.4-16.6 16.6-16.6h11.1c9.2 0 16.6 7.4 16.6 16.6v17c15.5.8 30.5 6.1 43 15.4 5.6 4.1 6.2 12.3 1.2 17.1L306 145.6c-3.8 3.7-9.5 3.8-14 1-5.4-3.4-11.4-5.1-17.8-5.1h-38.9c-9 0-16.3 8.2-16.3 18.3 0 8.2 5 15.5 12.1 17.6l62.3 18.7c25.7 7.7 43.7 32.4 43.7 60.1 0 34-26.4 61.5-59.1 62.4v16.8c0 9.2-7.4 16.6-16.6 16.6h-11.1c-9.2 0-16.6-7.4-16.6-16.6v-17c-15.5-.8-30.5-6.1-43-15.4-5.6-4.1-6.2-12.3-1.2-17.1l16.3-15.5c3.8-3.7 9.5-3.8 14-1 5.4 3.4 11.4 5.1 17.8 5.1h38.9c9 0 16.3-8.2 16.3-18.3 0-8.2-5-15.5-12.1-17.6l-62.3-18.7c-25.7-7.7-43.7-32.4-43.7-60.1.1-34 26.4-61.5 59.1-62.4zM480 352h-32.5c-19.6 26-44.6 47.7-73 64h63.8c5.3 0 9.6 3.6 9.6 8v16c0 4.4-4.3 8-9.6 8H73.6c-5.3 0-9.6-3.6-9.6-8v-16c0-4.4 4.3-8 9.6-8h63.8c-28.4-16.3-53.3-38-73-64H32c-17.7 0-32 14.3-32 32v96c0 17.7 14.3 32 32 32h448c17.7 0 32-14.3 32-32v-96c0-17.7-14.3-32-32-32z"></path></svg>'))
       ->add_fields( [
           Field::make( 'html', 'api_description_html', __('Html', 'kudos') )
                ->set_html('You can find this key in your <a target="_blank" href="https://mollie.com/dashboard/developers/api-keys">Mollie Dashboard</a> as soon as we have approved your website. Would you like to test the system first? Then you can use the test API key. To receive payments from your consumers, please use the live API key.'),
           Field::make( 'text', 'mollie_api_key', __( 'Mollie API Key', 'kudos' ) ),
           Field::make('html', 'check_api_key_button', null)
                ->set_html('
                    <input id="test_mollie_api_key" type="button" class="button button-secondary" value="Check Api Key">
                    <div id="check_key_spinner" class="spinner"></div>
                    <div id="result_message" class="hidden success message">Not hidden</div>
                '),
       ] )
	;

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
;