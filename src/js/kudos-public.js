import $ from 'jquery';
import {init as initModal, messageModal, donateModal} from "./kudos-modals";
import "jquery-validation";
import MicroModal from "micromodal";
import {library, dom} from "@fortawesome/fontawesome-svg-core";
import {faLock, faCircle} from "@fortawesome/free-solid-svg-icons";

library.add(faLock, faCircle);
dom.watch();

$(() => {

    const $body = $('body');
    let $kudosButtons = $('.kudos_btn');
    let $kudosModal;
    let redirectUrl;
    let order_id = new URLSearchParams(location.search).get('kudos_order_id');

    // Add kudos_mollie class and modal markup to body if button found
    if($kudosButtons.length) {
        $body.addClass('kudos_mollie');
    }

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
                if(result.success) {
                    let $content = '';
                    let data = result.data
                    let header = data.modal_header;
                    let message = data.modal_text;
                    $body.append($('\
                        <div id="kudos_message_modal" class="kudos_modal" aria-hidden="true">\
                            <div class="kudos_modal_overlay" tabindex="-1" data-micromodal-close>\
                                <div class="kudos_modal_container" role="dialog" aria-modal="true" aria-labelledby="kudos_modal-title">\
                                    <header class="kudos_modal_header">\
                                        <div class="kudos_modal_logo"></div>\
                                        <button class="kudos_modal_close" aria-hidden="true" aria-label="Close modal" data-micromodal-close></button>\
                                    </header>\
                                    <div id="kudos_modal_content" class="kudos_modal_content mt-4">\
                                        <div class="text-center">\
                                            <h2 class="font-normal">' + header + '</h2><p>' + message + '</p>\
                                        </div>\
                                        <footer class="kudos_modal_footer text-right">\
                                            <button class="kudos_btn kudos_btn_primary" type="button" data-micromodal-close aria-label="Close this dialog window">Ok</button>\
                                        </footer>\
                                    </div>\
                                </div>\
                            </div>\
                        </div>\
                    '));
                    MicroModal.show('kudos_message_modal', {
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
            let customHeader = $(this).data('customHeader');
            let customText = $(this).data('customText');

            MicroModal.show('kudos_form_modal', {
                onShow: function (modal) {
                    $(modal).find('#kudos_modal_title').html(customHeader);
                    $(modal).find('#kudos_modal_text').html(customText);
                },
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
                $kudosModal.addClass('kudos_loading');
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