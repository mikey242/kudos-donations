import $ from 'jquery';
import bootbox from 'bootbox';
import 'bootstrap/js/dist/modal';
import 'jquery-validation';
import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faLock, faCircle} from "@fortawesome/free-solid-svg-icons";
import logo from "../img/logo-colour.svg";

let modalLogo = new Image(40);
modalLogo.src = logo;

library.add(faLock, faCircle);
dom.watch();

$(function () {

    // Set validation defaults
    $.validator.setDefaults({
        errorElement: 'small',
    });


    const $body = $('body');
    let $kudosButtons = $('.kudos-button');

    let redirectUrl;
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
                    let message = $('<div class="top-content text-center"></div>');
                    switch (result.data.status) {
                        case 'paid':
                            message.append('<h2>Bedankt!</h2><p>Heel veel dank voor je donatie van €'+ result.data.value +'. Wĳ waarderen je steun enorm. Dankzĳ jouw inzet blĳft cultuur bereikbaar voor iedereen.</p>');
                            break;
                    }
                    bootbox.alert({
                        title: modalLogo,
                        centerVertical: true,
                        className: 'kudos-modal',
                        message: message,
                        buttons: {
                            ok: {
                                label: 'Ok',
                                className: 'ml-auto btn-primary',
                            }
                        }
                    })
                }
            },
            error: function (error) {
                console.log(error);
            }
        })
    }

    // Setup button action
    $kudosButtons.each(function() {
        $(this).click(function () {
            redirectUrl = $(this).data('redirect');
            let customText = $(this).data('customText');
            let topContent = $('\
                <div class="top-content text-center">\
                    <h2>Steun ons!</h2>\
                    <p>'+ customText +'</p>\
                </div>\
            ')
            let paymentBy = $('\
                <div class="payment-by mt-3 text-muted text-right"><small class="d-inline-block">\
                    <span class="fa-stack fa-xs align-middle">\
                        <i class="fas fa-circle fa-stack-2x"></i>\
                        <i class="fas fa-lock fa-stack-1x fa-inverse"></i>\
                    </span>\
                         Beveiligde betaling via\
                </small></div>\
                <i class="kudos-spinner fa-spin"></i> \
            ');
            let form = $('\
                <form id="kudos_form" action="">\
                    <input type="name" class="form-control mb-3" name="name" placeholder="Naam (optioneel)" />\
                    <input type="email" class="form-control mb-3" name="email_address" placeholder="E-mailadres (optioneel)" />\
                    <input required type="text" min="1" class="form-control" name="value" placeholder="Bedrag (in euro\'s) *" />\
                </form>\
                ');
            let message = topContent.add(form).add(paymentBy);
            bootbox.confirm({
                    title: modalLogo,
                    message: message,
                    className: 'kudos-modal',
                    centerVertical: true,
                    buttons: {
                        confirm: {
                            label: 'Doneer'
                        },
                        cancel: {
                            className: 'btn-outline-primary',
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
                                        required: "Vul een donatiebedrag in",
                                        min: "Minimum donatie is 1 euro"
                                    }
                                }
                            });
                            if(form.valid()) {
                                form.submit();
                                $(this).addClass('kudos-loading');
                                console.log(this);
                            }
                            return false;
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
                redirectUrl: redirectUrl,
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
