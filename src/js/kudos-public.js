import $ from 'jquery';
import bootbox from 'bootbox';
import 'bootstrap';
import 'jquery-validation';
import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faHeart, faLock, faCircle} from "@fortawesome/free-solid-svg-icons";
import logo from "../img/logo-colour.svg";

library.add(faHeart, faLock, faCircle);
dom.watch();

$(function () {

    // Set validation defaults
    $.validator.setDefaults({
        errorElement: 'small',
    });


    const $body = $('body');
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
                    if (result.data.status === 'paid')
                    bootbox.alert({
                        centerVertical: true,
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
    $('.kudos-button').each(function() {
        $(this).click(function () {
            redirectUrl = $(this).data('redirect');
            let topContent = $('\
                <div class="top-content text-center">\
                    <h2>Steun ons!</h2>\
                    <p>Wat lief dat je ons wilt steunen. <br/>Doneer eenmalig zonder verplichtingen.</p>\
                </div>\
            ')
            let paymentBy = $('\
                <div class="payment-by mt-3 text-muted text-right"><small class="d-inline-block">\
                    <span class="fa-stack fa-1x align-middle">\
                        <i class="fas fa-circle fa-stack-2x"></i>\
                        <i class="fas fa-lock fa-stack-1x fa-inverse"></i>\
                    </span>\
                         Beveiligde betaling via\
                </small></div>\
                <i class="kudos-spinner fa-spin"></i> \
            ');
            let form = $('\
                <form id="kudos_form" action="">\
                    <input type="name" class="form-control mb-3" name="name" placeholder="Naam" />\
                    <input type="email" class="form-control mb-3" name="email_address" placeholder="E-mailadres" />\
                    <input required type="text" min="1" class="form-control" name="value" placeholder="Bedrag (in euro\'s)" />\
                </form>\
                ');
            let message = topContent.add(form).add(paymentBy);
            let image = new Image(30);
            image.src = logo;
            bootbox.confirm({
                    title: image,
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
