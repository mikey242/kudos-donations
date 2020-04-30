import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faHandHoldingHeart, faCreditCard} from "@fortawesome/free-solid-svg-icons";
import {faIdeal, faPaypal} from "@fortawesome/free-brands-svg-icons";

library.add(faHandHoldingHeart, faCreditCard, faIdeal, faPaypal);
dom.watch();

($ => {
    'use strict';

    $(document).ready( function() {

        let $checkApiButton = $("#test_mollie_api_key");
        let $loader = $('#check_key_spinner');
        let $message = $('#result_message');

        $checkApiButton.click( function(e) {
            e.preventDefault();
            let formData = $('#theme-options-form').serialize();
            console.log(formData);
            $.ajax({
                method : "post",
                dataType : "json",
                url : wp_ajax.ajaxurl,
                data : {
                    formData: formData,
                    action: 'check_mollie_connection',
                },
                beforeSend: function() {
                    $loader.addClass('is-active');
                    $message.hide();
                },
                success:function(response){
                    if(response.success) {
                        $message.removeClass('text-error');
                        $message.addClass('text-success');
                    } else {
                        $message.removeClass('text-success');
                        $message.addClass('text-error');
                    }
                    $loader.removeClass('is-active');
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
