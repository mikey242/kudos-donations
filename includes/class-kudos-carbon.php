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
        Container::make( 'theme_options', sprintf(__('%s Settings', 'kudos-donations'), 'Kudos'))
	        ->set_page_file( 'kudos-settings' )
	        /* translators: %s: Plugin name */
            ->set_page_menu_title(sprintf(__('%s Settings', 'kudos-donations'), 'Kudos'))
            ->set_icon('data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>
            '))

	        /*
			 * Get started tab
			 */
	        ->add_tab(__('Get Started', 'kudos-donation'), [
	        	Field::make('html', 'welcome', null)
					->set_html('
						<h1>'. __('Get Started', 'kudos-donation') .'</h1>
						<br/>
						<div style="display: flex;">
							<img style="padding-right: 10px" width=40 src="data:image/svg+xml;base64,'. base64_encode('<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 555 449"><defs/><path fill="#2ec4b6" d="M0-.003h130.458v448.355H.001z"/><path fill="#ff9f1c" d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>') .'">
							<p>'. __("Welcome to Kudos. Follow these steps to start receiving donations.", 'kudos-donations') .'</p>
						</div>
					'),
		        Field::make('html', 'step_1', null)
		             ->set_html('
                        <h3>'.__("Step 1. Configure your payment method", 'kudos-donations').'</h3>
                        '/* translators: %s: Plugin name */.'
                        <p>'.sprintf(__("In the Mollie tab, fill in your payment settings. Kudos works with payment vendor %s.", 'kudos-donations'), "<a target='_blank' alt='Mollie website' href='https://www.mollie.com'>Mollie</a>") .'</p>
					'),
		        Field::make('html', 'step_2', null)
		             ->set_html('
                        <h3>'. __('Step 2. Configure look and feel', 'kudos-donations') .'</h3>
                        <p>'. __('Choose the design of the donation button under the Kudos Button tab. Review or change all text fields under the tabs Donation Form and Completed Payments.', 'kudos-donations') .'</p>
					'),
		        Field::make('html', 'step_3', null)
		            ->set_html('
                        <h3>'. __('Step 3. Add your first button', 'kudos-donations') .'</h3>
                        <p>'. __('There are two ways to place a donation button, depending on which WordPress text editor you use.', 'kudos-donations') .'</p>
                        <h4>'. __('[Option 1] - Using the block editor', 'kudos-donations') .'</h4>
                        <p>'. __('When using the block editor in a post or page, press the + sign to add a block. Search for ‘Kudos’. Select the Kudos Donations button option. Voila! You’ve created your first donations button.', 'kudos-donations') .'</p>
						<p><i>'. __('Tip: Keep the button call-to-action text short & sweet.', 'kudos-donations') .'</i></p>
						<h4>'. __('[Option 2] - Using the regular text editor', 'kudos-donations') .'</h4>
						<p>'. __('The regular WordPress editor uses shortcodes. A shortcode is a piece of text between brackets [like this]. To place a donations button, add the following code to a text field', 'kudos-donations') .':</p>
						<p><code>[kudos]</code></p>
						<p>'. __('The button will now appear on the page!', 'kudos-donations') .'</p>
						<p><i>'. __('Tip: You can customise each button and donate pop-up text by adding extra options to the shortcode. Check the ‘Shortcode’ tab for more information.', 'kudos-donations') .'</i></p>
		            '),
		        Field::make('html', 'step_4', null)
		             ->set_html('
                        <h3>'. __('Step 4. View your transactions', 'kudos-donations') .'</h3>
                        <p>'. sprintf(__('Check out the transactions table in the main Kudos menu to see your donations.', 'kudos-donations')) .'</p>
		            '),
		        Field::make('html', 'support', null)
		            ->set_html('
		                <h3>'. __('Need support?', 'kudos-donations') .'</h3>
		                <p>'. __('We tried to make the Kudos Donations plugin as easy to use as possible. But we all need a little help sometimes.', 'kudos-donations') .'</p>
		                '/* translators: %s: Email address */.'
						<p>'. sprintf(__('If you have questions about the Kudos plugin or require support with the installation, please get in touch via %s.', 'kudos-donations'), '<a href="mailto:kudos@iseard.media">kudos@iseard.media</a>') .'</p>
						'/* translators: %s: Iseard Media website link */.'
					 	<p>'. sprintf(__('This plugin was created by %s'), '<a target="_blank" href="https://iseard.media">iseard.media</a>') .'</p>
		            ')
	        ])

	        /*
			 * Mollie tab
			 */
	        ->add_tab('Mollie', [
                Field::make( 'html', 'payment_intro', null )
                    ->set_html('
						<h1>Mollie</h1>
						'/* translators: %s: Mollie website link */.'
						<p><strong>' . sprintf(__('Kudos works with payment vendor Mollie. Create a %s.', 'kudos-donations'), '<a target="_blank" href="https://mollie.com/">Mollie account</a>') . '</strong></p>
						<p>'. __('To receive payments, you will need to add your Mollie Live API key in the box below. Would you like to test the system first? Then you can use the Mollie Test API key.', 'kudos-donations') .'</p>
						'./* translators: %s: Link to Mollie dashboard */'
						<p>'. sprintf(__('Both "Live" and "Test" API keys can be found in your %s', 'kudos-donations'), '<a target="_blank" href="https://mollie.com/dashboard/developers/api-keys">Mollie Dashboard</a>') .'.</p>
					'),
                Field::make('radio', 'kudos_mollie_api_mode', __('Mode', 'kudos-donations'))
	                ->set_help_text( __('When using this plugin for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you\'re ready to receive live payments you can switch the mode to "Live".', 'kudos-donations')  )
                    ->add_options([
                        'test' => __('Test', 'kudos-donations'),
                        'live' => __('Live', 'kudos-donations')
                    ])
	                ->set_default_value('test'),
                Field::make( 'text', 'kudos_mollie_test_api_key', __( 'Mollie Test API Key', 'kudos-donations' ) )
	                /* translators: %s: Start of API key */
		            ->set_attribute('placeholder', sprintf(__('This begins with "%s"', 'kudos-donations'), 'test_')),
                Field::make( 'text', 'kudos_mollie_live_api_key', __( 'Mollie Live API Key', 'kudos-donations' ) )
	                /* translators: %s: Start of API key */
	                ->set_attribute('placeholder', sprintf(__('This begins with "%s"', 'kudos-donations'), 'live_')),
                Field::make('html', 'check_api_key_button', null)
                    ->set_html('
                        <input id="test_mollie_api_key" type="button" class="button button-secondary" value="'. __("Check API Key", "kudos-donations") .'">
                        <div id="check_key_spinner" class="spinner"></div>
                        <div id="result_message" class="hidden message"></div>
                    ')
            ] )

	        /*
			 * Kudos Button tab
			 */
	        ->add_tab(__('Kudos Button', 'kudos-donations'), [
				Field::make('html', 'button_intro', null)
				     ->set_html('
							<h1>'. __( 'Kudos button', 'kudos-donations' ) .'</h1>
							<p>'. __( 'Set the colour and text of your donation button below.', 'kudos-donations' ) .'</p>
		                '),
		        Field::make('radio_image', 'kudos_button_style', __('Button style', 'kudos-donations'))
		             ->add_options([
			             'style-orange' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAyCAIAAACCil8SAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kb9LQlEUxz9pYpRhUUNDg4g1ZZhB1NKglAXVoAZZLfr8Fag93jMiWoNWoSBq6ddQf0GtQXMQFEUQTQ3NRS0lr/M0MCLP5dzzud97z+Hec8ESzSl5vdEH+UJRC4cCrrnYvMv+jA0L7XhxxxVdnY6MR6lrH3c0mPHGa9aqf+5fa0mmdAUamoRHFVUrCk8IT60WVZO3hTuVbDwpfCrcp8kFhW9NPVHlF5MzVf4yWYuGg2BpE3ZlfnHiFytZLS8sL8eTz60oP/cxX+JIFWYjEt3i3eiECRHAxSRjBBligBGZh6Q7fvplRZ18XyV/hmXJVWRWWUNjiQxZivSJuiLVUxLToqdk5Fgz+/+3r3p60F+t7giA7ckw3nrAvgXlkmF8HhpG+Qisj3BRqOUvH8Dwu+ilmubZB+cGnF3WtMQOnG9C14Ma1+IVySpuSafh9QRaY9BxDc0L1Z797HN8D9F1+aor2N2DXjnvXPwGce5n6w3W25IAAAAJcEhZcwAACxMAAAsTAQCanBgAAAJESURBVGiB7Zo/aBNRHMc/edaKkiIEhBRidMggKUKhUyQu7RAnF2lGF7s5GRFUENzEwXTRrcHFyTh1alGiYjTFQYKSgBARbZQDIfjnaiFGzyVGk5Le72xf3wXyWcLL+/K7D+HH+12OCziOQw9vF6k/5PNrvtRorffuGmWka1UvsHIF+70hGXf+0X15kxfXzZmIUO3PV7f87woEHMfhwyPun4YNTew/FK01nl4YCFdAsfqA75ZpDSmKd8umHTygaFRMO3hAsf7JtIMHFD++mXbwgHKP+Imhrk4GTHfEPbIJp54wdvjvsrXGnSOb5UNxYrOEE4QmABoVKgu8uSe/4NZ05QQjJLOEE11fhiY4Ps94guJ5YZkdaYZoipNLva4dYum+WxvQrxubZXqB0f0uGRmadaMpkln3WPCgsJ5O3WCE5I3tLalTdzrn0gMd7FVhSW26kxlCcWm4lhcG9egGI0yek4arOaySMKtHNzkvTVZzPL8qL2x6CI+OeYrr0S2KOyGW9nR66NG165TF/eCLqVbO0qhKw76YaoUzNL+Kkr6Yanadwtz2ltR8Mlglihn3mPmp1qGWZynt0hWGp1oPVonFVF+nWl4+1bb2b+Jnk1/NrmU/7DrFDOUs8TnCifbthLVC7a78pwUCzu3If9vuPKaHsEeGujoZ6upEsdvbHadZFHsPmHbwgGo/rhoQFIdSph08oIieYN+4aQ0pil17OHbNtIYUBRCZYeqiaRMRf87do2eZumTURESg6/WLj495dtnP7zP8Bq4Aja10468CAAAAAElFTkSuQmCC',
			             'style-green' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAyCAIAAACCil8SAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kb9LQlEUxz9pYpRhUUNDg4g1ZZhB1NKglAXVoAZZLfr8Fag93jMiWoNWoSBq6ddQf0GtQXMQFEUQTQ3NRS0lr/M0MCLP5dzzud97z+Hec8ESzSl5vdEH+UJRC4cCrrnYvMv+jA0L7XhxxxVdnY6MR6lrH3c0mPHGa9aqf+5fa0mmdAUamoRHFVUrCk8IT60WVZO3hTuVbDwpfCrcp8kFhW9NPVHlF5MzVf4yWYuGg2BpE3ZlfnHiFytZLS8sL8eTz60oP/cxX+JIFWYjEt3i3eiECRHAxSRjBBligBGZh6Q7fvplRZ18XyV/hmXJVWRWWUNjiQxZivSJuiLVUxLToqdk5Fgz+/+3r3p60F+t7giA7ckw3nrAvgXlkmF8HhpG+Qisj3BRqOUvH8Dwu+ilmubZB+cGnF3WtMQOnG9C14Ma1+IVySpuSafh9QRaY9BxDc0L1Z797HN8D9F1+aor2N2DXjnvXPwGce5n6w3W25IAAAAJcEhZcwAACxMAAAsTAQCanBgAAAKRSURBVGiB7ZrPb9JgGMfLK+GHjBFLbJPaqTFDtkRJdOxgYjTE+7h58raLXnR/gBizLTHxtGSHedCbu3gbGmO8GCMaTVhMthm2lSmbULOOwQC7DrTgAcMQhT4NvntLwucCL3zz8CG87dOH1FSpVKg/eZH+FsluxeVCQpGVskoZCXP94k12697ap1RRIWWjyb7uw+Ta9PoqQRUIqPrwKPnZ+K5UVfftTnp6fYW0CQi0q6p3hcXGw82ooNdZSSrtkdaAgl5tb5J20AFalvOkHXSAtktF0g46QN/Vn6QddIBIC+ijq4uTDtM1a0ea82zocp/tcG25q6oX3r9skfc6nCMM73fRA45eiqKW5fxjMfFUSsE/sS1dOJzVPu7xDbvo+hcHHL2THt+wi74jLALrHMRmCNDsk3MXG1xrBBne3+Stv8GuO8Icmxo87zzU6mcMMjywGl7dAM1OeHyaMc5mBxbEqMtZ7ROntV11gVF3anCo9R6oIe5Bp0NcujeOe7wOJzA8JyWBSSy6nNV+va8fGJ4VE9FcBhjGogvfsrNi4v6XGLwy4SbcY9bXp7DohlYXgMkgw497zsIrY9EVi8qDr3Fg2BBdbWZDWJELwLAhutpYbL4Am6wM0dXEojIWm/+/NfGeGaK5TEjQPuzId7UaYSk1uvSh9a4g3NUaiOYyVz9Gwk2mhrCUgne1tqaJUrn8o1LeX9Y9b0AsKiFhYWZDuMad9Lvc1cuJaD4zt5ls9jX+ickXed6O8QHTYZNwVxcnXV2coB7YOGUQkNtiJe2gA1T9u6pTQAE3S9pBB+iKm2UtNtIaUJDFhG73nyGtAQVRFHXpyNGbJ7ykTUD8Pu+O8qdudYKxqf72i3c76cn4kpHvZ/gFXzjMlNqW1W8AAAAASUVORK5CYII=',
		             ])
		             ->set_default_value('style-orange'),
		        Field::make('text', 'kudos_button_label', __('Button label', 'kudos-donations'))
		             ->set_default_value(__('Donate now', 'kudos-donations')),
	        ])

	        /*
			 * Donation Form tab
			 */
			->add_tab(__('Donation Form', 'kudos-donations'), [
		        Field::make('html', 'form_intro', null)
		             ->set_html('
						<h1>'. __('Donation Form', 'kudos-donations') .'</h1>
						<p>'. __( 'This is where you can change the settings of the donation pop-up that appears when you click on the donations button.', 'kudos-donations' ) .'</p>
	                '),
		        Field::make('radio', 'kudos_name_required', __('Name', 'kudos-donations'))
		             ->add_options([
			             true => __('Required', 'kudos-donations'),
			             false => __('Optional', 'kudos-donations')
		             ])
		             ->set_default_value(true),
		        Field::make('radio', 'kudos_email_required', __('Email', 'kudos-donations'))
		             ->add_options([
			             true => __('Required', 'kudos-donations'),
			             false => __('Optional', 'kudos-donations')
		             ])
		             ->set_default_value(true),
		        Field::make('text', 'kudos_form_header', __('Payment form header', 'kudos-donations'))
		             ->set_default_value(__('Support us!', 'kudos-donations')),
		        Field::make('textarea', 'kudos_form_text', __('Payment form text', 'kudos-donations'))
		             ->set_default_value(__('Thank you for your donation. We appreciate your support!', 'kudos-donations')),
			])

			/*
			 * Completed payment tab
			 */
			->add_tab(__('Completed Payment', 'kudos-donations'), [
			    Field::make('html', 'completed_payment_intro', null)
			        ->set_html('
						<h1>'. __('Completed Payment', 'kudos-donations') .'</h1>
						<p>'. __( 'This is where you can change the settings of the \'thank you\' pop-up that appears after a payment has been made.', 'kudos-donations' ) .'</p>
			        '),
			    Field::make('html', 'popup_intro', null)
			        ->set_html('
			            <h3><strong>'. __('Pop-up message', 'kudos-donations') .'</strong></h3>
			            './* translators: %s: Available handlebar variables */'
			            <p>'. sprintf(__('Use the following variables %s to include the donated amount and donor\'s name in displayed text.', 'kudos-donations'), '{{value}}, {{name}}') .'</p>
			        '),
			    Field::make('checkbox', 'kudos_return_message_enable', __('Show pop-up message when payment complete', 'kudos-donations'))
			         ->set_help_text( __('Enable this to show a pop-up thanking the customer for their donation.', 'kudos-donations')  )
			         ->set_default_value(true),
			    Field::make('text', 'kudos_return_message_header', __('Message header', 'kudos-donations'))
			         ->set_default_value(__('Thank you!', 'kudos-donations'))
			         ->set_conditional_logic([
			             [
				             'field' => 'kudos_return_message_enable',
				             'value' => true
			             ]
			         ]),
			    Field::make('textarea', 'kudos_return_message_text', __('Message text', 'kudos-donations'))
			        /* translators: %s: Value of donation */
			         ->set_default_value(sprintf(__('Many thanks for your donation of €%s. We appreciate your support.', 'kudos-donations'), '{{value}}'))
			         ->set_conditional_logic([
			             [
				             'field' => 'kudos_return_message_enable',
				             'value' => true
			             ]
			         ]),
			    Field::make('html', 'return_url_intro', null)
			         ->set_html('
			            <h3><strong>'. __('Return url', 'kudos-donations') .'</strong></h3>
			            <p>'. __('After payment the customer is returned to the page where they clicked on the donation button. To use a different return URL, select the box below.', 'kudos-donations') .'</p>
			        '),
			    Field::make('checkbox', 'kudos_custom_return_enable', __('Use custom return URL', 'kudos-donations'))
			         ->set_default_value(false),
			    Field::make('text', 'kudos_custom_return_url', __('URL', 'kudos-donations'))
				    /* translators: %s: Current web address (e.g https://mywebsite.com) */
			         ->set_help_text( sprintf(__('e.g: %s/thanks', 'kudos-donations'), get_site_url()) )
			         ->set_conditional_logic([
			             [
				             'field' => 'kudos_custom_return_enable',
				             'value' => true
			             ]
			         ]),
			])

	        /*
	         * Shortcode tab
	         */
            ->add_tab(__('Shortcode', 'kudos-donations'), [
	            Field::make('html', 'advanced_intro', null)
	                ->set_html('
						<h1>'. __('Shortcode', 'kudos-donations') .'</h1>
	                '),
	            Field::make('html', 'shortcode_intro', null)
	                ->set_html('
	                    <h3><strong>'. __('Shortcode options', 'kudos-donations') .'</strong></h3>
	                    './* translators: %s: <code>[kudos]</code>*/'
	                    <p>'. sprintf(__('Override the default settings %s by using the following shortcode options', 'kudos-donations'), '<code>[kudos]</code>') .':</p>
	                '),
	            Field::make('html', 'shortcode_options', null)
	                ->set_html('
	                    <p><code>[kudos button="'. __('Help us out!', 'kudos-donations') .'"]</code> - '. __('Customise button text', 'kudos-donations') .'</p>
						<p><code>[kudos header="'. __('Support our cause', 'kudos-donations') .'"]</code> - '. __('Customise header text', 'kudos-donations') .'</p>
						<p><code>[kudos body="'. __('Thank you for donating to this project. We really appreciate your support.', 'kudos-donations') .'"]</code> - '. __('Customise message text', 'kudos-donations') .'</p>
						<p>'. __('A fully customised button and text would look like this', 'kudos-donations') .':</p>
						<p><code>[kudos button="'. __('Help us out!', 'kudos-donations') .'" header="'. __('Support our cause', 'kudos-donations') .'" body="'. __('Thank you for donating to this project. We really appreciate your support.', 'kudos-donations') .'"]</code></p>
	                '),
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
        Block::make( 'Kudos Button')
            ->set_icon('heart')
	        ->set_description(__('Adds a Kudos donate button to your post or page.', 'kudos-donations'))
	        ->set_category('widgets', 'common')
            ->add_fields([
                Field::make('text', 'kudos_button_label', __('Button label', 'kudos-donations'))
	                ->set_default_value(get_option('_kudos_button_label')),
	            Field::make('text', 'kudos_modal_header', __('Pop-up header', 'kudos-donations'))
	                 ->set_default_value(get_option('_kudos_form_header')),
                Field::make('text', 'kudos_modal_text', __('Pop-up text', 'kudos-donations'))
	                ->set_default_value(get_option('_kudos_form_text')),
            ])
            ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
                $atts['button'] = $fields['kudos_button_label'];
                $atts['body'] = $fields['kudos_modal_text'];
                $atts['header'] = $fields['kudos_modal_header'];
                $classes = $attributes ? $attributes['className'] : '';

                    echo "<div class='kudos_block_button ". esc_attr($classes) ."'>";
						$button = new Kudos_Button($atts);
						$button->get_button();
                    echo "</div><!-- /.block -->";

            } );
    }
}

new Carbon();