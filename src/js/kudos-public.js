import $ from 'jquery';
import {init as initModal, messageModal, donateModal} from "./kudos-modals";
import "jquery-validation";
import Mustache from "mustache";
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
        $kudosModal = initModal();
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
                    let message;
                    let data = result.data
                    switch (data.transaction.status) {
                        case 'paid':
                        case 'open':
                            message = Mustache.render(data.modalText, {value: Math.round(data.transaction.value)});
                            break;
                    }
                    $content = messageModal(data.modalHeader, message);
                    MicroModal.show('kudos_modal', {
                        onShow: modal => $(modal).find('#kudos_modal_content').html($content),
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

            let $content = donateModal(customHeader, customText);

            MicroModal.show('kudos_modal', {
                onShow: modal => $(modal).find('#kudos_modal_content').html($content),
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