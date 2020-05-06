import $ from 'jquery';
import "jquery-validation";
import MicroModal from "micromodal";

$(() => {

    const $body = $('body');
    let $kudosButtons = $('.kudos_button_donate');
    let redirectUrl;
    let order_id = new URLSearchParams(location.search).get('kudos_order_id');

    // Set validation defaults
    $.validator.setDefaults({
        errorElement: 'small',
    });

    if($kudosButtons.length) {
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
                    onClose: function (modal) {
                        $(modal).find('form').validate().resetForm();
                        document.getElementById('kudos_form').reset();
                    },
                    awaitCloseAnimation: true
                });
            })
        })
    }

    // Check order status if query var exists
    if(order_id) {
        $.ajax({
            method: 'post',
            dataType: 'json',
            url: kudos.ajaxurl,
            data: {
                action: 'check_transaction',
                order_id: order_id
            },
            success: function (result) {
                if(result.success && result.data.trigger) {
                    let data = result.data
                    let header = data.modal_header ? data.modal_header : '';
                    let message = data.modal_text ? data.modal_text : '';
                    $body.append($(`
                        <div id="kudos_message_modal" class="kudos_modal" aria-hidden="true">
                            <div class="kudos_modal_overlay" tabindex="-1" data-micromodal-close>
                                <div class="kudos_modal_container" role="dialog" aria-modal="true" aria-labelledby="kudos_modal-title">
                                    <header class="kudos_modal_header">
                                        <div class="kudos_modal_logo"></div>
                                        <button class="kudos_button kudos_modal_close" aria-hidden="true" aria-label="Close modal" data-micromodal-close></button>
                                    </header>
                                    <div id="kudos_modal_content" class="kudos_modal_content mt-4">
                                        <div class="text-center">
                                            <h2 class="font-normal">` + header + `</h2><p>` + message + `</p>
                                        </div>
                                        <footer class="kudos_modal_footer text-right">
                                            <button class="kudos_button style-orange" type="button" data-micromodal-close aria-label="Close this dialog window">Ok</button>\
                                        </footer>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `));
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
                name: {
                    required: kudos.name_required
                },
                email_address: {
                    required: kudos.email_required,
                    email: kudos.email_invalid
                },
                value: {
                    required: kudos.value_required,
                    min: kudos.value_minimum,
                    digits: kudos.value_digits
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
        let $form = $('#kudos_form_modal');
        let $kudosErrorMessage = $('.kudos_error_message');

        $.ajax({
            method: 'post',
            dataType: 'json',
            url: kudos.ajaxurl,
            data:  {
                action: 'create_payment',
                redirectUrl: redirectUrl,
                form: $(e.currentTarget).serialize()
            },
            beforeSend: function() {
                $form.addClass('kudos_loading');
            },
            success: function (result) {
                console.log(result)
                if(result.success) {
                    $(location).attr('href', result.data);
                } else {
                    $kudosErrorMessage.text(result.data.message);
                    $form.removeClass('kudos_loading').addClass('error');
                }
            },
            error: function (error) {
                console.log('error', error)
            }
        })
    })
})