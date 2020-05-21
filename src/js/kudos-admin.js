import $ from "jquery"
import {dom, library} from "@fortawesome/fontawesome-svg-core"
import {faCreditCard} from "@fortawesome/free-solid-svg-icons"
import {faIdeal, faPaypal} from "@fortawesome/free-brands-svg-icons"

library.add(faCreditCard, faIdeal, faPaypal);
dom.watch();

$(() => {
    'use strict';

    let $checkApiButton = $("#test_mollie_api_key");
    let $sendTestEmailButton = $("#send_test_email");

    $sendTestEmailButton.click( function(e) {

        e.preventDefault();

        let $loader = $('#send_email_spinner');
        let $message = $('#email_result_message');
        let email = $('#test_email_address').val();

        // Validate email
        if(!validateEmail(email)) {
            $message.addClass('text-error');
            $message.text(kudos.email_invalid).css('display', 'inline-block');
            return;
        }

        $.ajax({
            method : "post",
            dataType : "json",
            url : kudos.ajaxurl,
            data : {
                email: email,
                action: 'send_test_email',
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
            },
            error: function(errorThrown){
                console.log(kudos.ajaxurl, errorThrown);
            }
        });
    });

    $checkApiButton.click( function(e) {

        e.preventDefault();

        let $loader = $('#check_key_spinner');
        let $message = $('#api_result_message');
        let formData = $('#theme-options-form').serialize();

        $.ajax({
            method : "post",
            dataType : "json",
            url : kudos.ajaxurl,
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
            },
            error: function(errorThrown){
                console.log(kudos.ajaxurl, errorThrown);
            }
        });
    });

    function validateEmail(email) {
        let emailReg = /^([\w-.]+@([\w-]+\.)+[\w-]{2,6})?$/;
        return emailReg.test( email );
    }
})
