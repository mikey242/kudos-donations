import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faHandHoldingHeart} from "@fortawesome/free-solid-svg-icons";

library.add(faHandHoldingHeart);
dom.watch();

(function( $ ) {
    'use strict';

    $(document).ready( function() {

        let $checkApiButton = $("#test_mollie_api_key");
        let $loader = $('#check_key_spinner');
        let $message = $('#result_message');

        $checkApiButton.click( function(e) {
            e.preventDefault();
            let apiKey =  $('input[name="carbon_fields_compact_input[_mollie_api_key]"]').val();
            $.ajax({
                method : "post",
                dataType : "json",
                url : wp_ajax.ajaxurl,
                data : {
                    action: 'check_mollie_connection',
                    apiKey: apiKey
                },
                beforeSend: function() {
                    $loader.addClass('is-active');
                    $message.hide();
                },
                success:function(response){
                    $loader.removeClass('is-active');
                    $message.addClass('text-success');
                    $message.text(response.data).css('display', 'inline-block');
                    console.log(response, $message);
                },
                error: function(errorThrown){
                    console.log(wp_ajax.ajaxurl, errorThrown);
                }
            });
        });
    });

})( jQuery );
