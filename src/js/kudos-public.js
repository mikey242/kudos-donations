import $ from 'jquery';
import "jquery-validation";
import MicroModal from "micromodal";
import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faLock, faCircle} from "@fortawesome/free-solid-svg-icons";
import logo from "../img/logo-colour.svg";

library.add(faLock, faCircle);
dom.watch();

$(function () {

    const $body = $('body');
    let $kudosButtons = $('.kudos-button');
    if($kudosButtons.length) {
        $body.addClass('kudos-mollie');
    }

    let modalLogo = new Image(40);
    modalLogo.src = logo;

    let $kudosModal = $('\
        <div id="kudos-modal" class="kudos-modal text-gray-900 text-base" aria-hidden="true">\
            <div class="kudos_modal_overlay flex justify-center items-center fixed top-0 left-0 w-full h-full bg-gray-900 z-50" tabindex="-1" data-micromodal-close>\
                <div class="kudos_modal_container bg-white py-4 px-8 rounded-lg max-h-screen max-w-lg relative overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="kudos-modal-title">\
                    <header class="kudos_modal_header flex items-center justify-between">\
                        <div class="kudos_modal_title mt-0 mb-0" id="kudos-modal-title">\
                        '+modalLogo.outerHTML+'\
                        </div>\
                        <button class="kudos_modal_close text-black p-0" aria-hidden="true" aria-label="Close modal" data-micromodal-close></button>\
                    </header>\
                    <main class="kudos_modal_content" id="kudos-modal-content"></main>\
                </div>\
            </div>\
        </div>\
    ');

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
                    let $content = '';
                    switch (result.data.status) {
                        case 'paid':
                        case 'open':
                            $content = $('\
                                <div class="top-content text-center">\
                                    <h2 class="font-normal">Bedankt!</h2><p>Heel veel dank voor je donatie van €'+ result.data.value +'. Wĳ waarderen je steun enorm. Dankzĳ jouw inzet blĳft cultuur bereikbaar voor iedereen.</p>\
                                </div>\
                                <footer class="kudos_modal_footer mt-4 text-right">\
                                    <button class="kudos_modal_btn kudos_modal_btn-primary" type="button" data-micromodal-close aria-label="Close this dialog window">Ok</button>\
                                </footer>\
                            ');
                            break;
                    }
                    $body.append($kudosModal);
                    MicroModal.show('kudos-modal', {
                        onShow: modal => $(modal).find('#kudos-modal-content').html($content),
                        awaitCloseAnimation: true
                    });
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

            let content = $('\
                <div class="top-content text-center">\
                    <h2 class="font-normal">Steun ons!</h2>\
                    <p>'+ customText +'</p>\
                </div>\
                <form id="kudos_form" action="">\
                    <input type="text" class="appearance-none border-2 rounded w-full py-2 px-3 text-gray-700 focus:border-orange-500" name="name" placeholder="Naam (optioneel)" />\
                    <input type="email" class="mt-3 appearance-none border-2 rounded w-full py-2 px-3 text-gray-700 focus:border-orange-500" name="email_address" placeholder="E-mailadres (optioneel)" />\
                    <input required type="text" min="1" class="mt-3 appearance-none border-2 rounded w-full py-2 px-3 text-gray-700 focus:border-orange-500" name="value" placeholder="Bedrag (in euro\'s) *" />\
                    <div class="payment-by mt-3 text-muted text-right"><small class="text-gray-600">\
                        <span class="fa-stack fa-xs align-middle">\
                            <i class="fas fa-circle fa-stack-2x"></i>\
                            <i class="fas fa-lock fa-stack-1x fa-inverse"></i>\
                        </span>\
                        Beveiligde betaling via\
                    </small></div>\
                    <footer class="kudos_modal_footer mt-4 text-center">\
                        <button class="kudos_modal_btn kudos_modal_btn-secondary" type="button" data-micromodal-close aria-label="Close this dialog window">Annuleren</button>\
                        <button id="kudos_submit" class="kudos_modal_btn kudos_modal_btn-primary" type="submit">Doneeren</button>\
                    </footer>\
                </form>\
                <i class="kudos-spinner fa-spin"></i> \
            ');
            $body.append($kudosModal);
            MicroModal.show('kudos-modal', {
                onShow: modal => $(modal).find('#kudos-modal-content').html(content),
                awaitCloseAnimation: true
            });
        })
    })

    // Check form before submit
    $body.on('click', '#kudos_submit', function (e) {
        e.preventDefault();
        let $form = $(this.form);
        $form.validate({
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
        })
        if($(this.form).valid()) {
            $form.submit();
        }
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
            beforeSend: function() {
                $kudosModal.addClass('kudos-loading');
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