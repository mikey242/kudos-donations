import $ from 'jquery';
import bootbox from 'bootbox';
import 'bootstrap';
import 'jquery-validation';
import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faHeart} from "@fortawesome/free-solid-svg-icons";

library.add(faHeart);
dom.watch();

$(function () {

    const $body = $('body');
    let order_id = new URLSearchParams(location.search).get('kudos_order_id');

    // Check order status if query var exists
    if(order_id) {
        $.ajax({
            method: 'post',
            dataType: 'json',
            url: wp_ajax.ajaxurl,
            data: {
                action: 'check_transaction',
                order_id: order_id
            },
            success: function (result) {
                console.log(result);
                if(result.success) {
                    if (result.data.status === 'paid')
                    bootbox.alert({
                        message: 'Thanks for your donation of €' + result.data.value
                    })
                }
            },
            error: function (error) {
                console.log(error);
            }
        })
    }

    // Setup button action
    $('.kudos_button a').each(function() {
        $(this).click(function () {
            let form = $("\
                <form id='kudos_form' action=''>\
                    <input required type='email' class='form-control' name='email_address' placeholder='E-mail adres' /><br/>\
                    <input required type='text' min='1' class='form-control' name='value' placeholder='Bedrag (5, 10, 20)' />\
                </form>"
                );
            bootbox.confirm({
                    title: 'Kudos donation <i class="fas fa-heart"></i>',
                    message: form,
                    className: 'kudos-modal',
                    buttons: {
                        confirm: {
                            label: 'Doneer'
                        },
                        cancel: {
                            label: 'Annuleren'
                        }
                    },
                    callback: function (result) {
                        if(result) {
                            let validator = form.validate({
                                rules: {
                                    value: {
                                        digits: true
                                    }
                                },
                                messages: {
                                    value: {
                                        required: "Oops, looks like you forgot something",
                                        min: "Minimum donation is 1 euro"
                                    }
                                }
                            });
                            console.log(validator);
                            if(form.valid()) {
                                form.submit();
                            } else {
                                return false;
                            }
                        }
                    },
                }
            );
        })
    })

    // Submit donation form action
    $body.on('submit', 'form#kudos_form', function (e) {
        e.preventDefault();
        $.ajax({
            method: 'post',
            dataType: 'json',
            url: wp_ajax.ajaxurl,
            data:  {
                action: 'create_payment',
                redirectUrl: $(location).attr('href').split('?kudos_order_id')[0],
                form: $(e.currentTarget).serialize()
            },
            success: function (result) {
                if(result.success) {
                    $(location).attr('href', result.data);
                }
            },
            error: function (error) {
                console.log('error', error)
            }
        })
    })
})
