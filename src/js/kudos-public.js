import $ from 'jquery'
import MicroModal from "micromodal"
import '../img/logo-colour-40.png' //used as email attachment

$(() => {
    'use strict';

    const $body = $('body');
    let $kudosButtons = $('.kudos_button_donate');

    // Set validation defaults
    $.validator.setDefaults({
        errorElement: 'small',
    });

    if($kudosButtons.length) {
        // Setup button action
        $kudosButtons.each(function() {
            $(this).click(function () {
                let $target = $(this).data("target")
                if($target) {
                    MicroModal.show($target, {
                        onClose: function (modal) {
                            let $form = $(modal).find('#kudos_form');
                            if($form.length) {
                                $form.validate().resetForm();
                                // $form.reset();
                            }
                        },
                        awaitCloseAnimation: true
                    });
                }
            })
        })
    }

    // Show message modal if exists
    if($('#kudos_modal-message').length) {
        MicroModal.show('kudos_modal-message', {
            awaitCloseAnimation: true,
            awaitOpenAnimation: true
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
    $body.on('submit', 'form.kudos_form', function (e) {
        e.preventDefault();
        let $kudosFormModal = $(this).closest('.kudos_form_modal');
        let $kudosErrorMessage = $('.kudos_error_message');

        $.ajax({
            method: 'post',
            dataType: 'json',
            url: kudos.ajaxurl,
            data:  {
                action: 'create_payment',
                form: $(e.currentTarget).serialize()
            },
            beforeSend: function() {
                $kudosFormModal.addClass('kudos_loading');
            },
            success: function (result) {
                if(result.success) {
                    $(location).attr('href', result.data);
                } else {
                    $kudosErrorMessage.text(result.data.message);
                    $kudosFormModal.removeClass('kudos_loading').addClass('error');
                }
            },
            error: function (error) {
                console.log('error', error)
            }
        })
    })
})