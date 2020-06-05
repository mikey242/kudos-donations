import $ from 'jquery'
import MicroModal from "micromodal"
// import validator from 'jquery-validation';
import '../img/logo-colour-40.png' //used as email attachment

$(() => {
    'use strict';

    const $body = $('body');
    let $kudosButtons = $('.kudos_button_donate');
    let animating = false;

    // Set validation defaults
    $.validator.setDefaults({
        ignore: [],
        errorElement: 'small',
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
    });

    if($kudosButtons.length) {
        // Setup button action
        $kudosButtons.each(function() {
            $(this).click(function () {
                let $target = $(this).data("target")
                if($target) {
                    MicroModal.show($target, {
                        onShow: function (modal) {
                            $(modal).find('.kudos_error_message').text('');
                            let $form = $(modal).find('.kudos_form');
                            if($form.length) {
                                $('fieldset.current-tab').removeClass('current-tab');
                                $('fieldset:first-child').addClass('current-tab');
                                $form.validate().resetForm();
                                $form[0].reset();
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

    // Multi step form navigation
    $('.kudos_form_nav').on('click', function () {
        if(animating) return false;
        let $current_tab = $(this).closest('.form-tab');
        let $modal = $(this).closest('.kudos_modal_container');
        let direction = $(this).data('direction');
        let $next_tab = $current_tab.prev();
        if(direction === 'next') {
            $next_tab = $current_tab.next();
            $current_tab.find('input').validate();
            if(!$current_tab.find('input').valid()) {
                return;
            }
        }

        // Begin animation
        animating = true;
        $modal.animate({opacity:0}, {
            step: function (now, mx) {
                let position = (1 - now) * 50;
                $modal.css({
                    'transform': 'translateX(' + (direction === 'next' ? '-' : '') + position + 'px)'
                })
            },
            duration: 100,
            easing: 'linear',
            complete: function () {
                $current_tab.removeClass('current-tab');
                $next_tab.addClass('current-tab');
                $modal.animate({opacity:1}, {
                    step: function (now, mx) {
                        let position = (1 - now) * 50;
                        $modal.css({
                            'transform': 'translateX(' + (direction === 'next' ? '' : '-') + position + 'px)'
                        })
                    },
                    duration: 100,
                    easing: 'linear',
                    complete: function () {
                        animating = false;
                    }
                })
            }
        })
    })

    // Check form before submit
    $body.on('click', '.kudos_submit', function (e) {
        e.preventDefault();
        let $form = $(this.form);
        $form.validate()
        if($form.valid()) {
            $form.submit();
        }
    })

    // Submit donation form action
    $body.on('submit', 'form.kudos_form', function (e) {
        e.preventDefault();
        let $kudosFormModal = $(this).closest('.kudos_form_modal');
        let $kudosErrorMessage = $kudosFormModal.find('.kudos_error_message');

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