import $ from 'jquery'
import MicroModal from "micromodal"
import validator from 'jquery-validation';
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
        let $inputs = $current_tab.find(':input');

        // Validate fields before proceeding
        if(direction === 'next') {
            $inputs.validate();
            if(!$inputs.valid()) {
                return;
            }
        }

        // Calculate next tab
        let $next_tab = $current_tab;
        let change = false;
        while(!change) {
            $next_tab = (direction === 'next' ? $next_tab.next() : $next_tab.prev());
            change = check_requirements($next_tab);
            // console.log(change, $next_tab);
        }

        // Begin animation
        animating = true;
        let offset = 25;
        let duration = 150;
        $modal.animate({opacity:0}, {
            step: function (now) {
                let position = (1 - now) * offset;
                $modal.css({
                    'transform': 'translateX(' + (direction === 'next' ? '-' : '') + position + 'px)'
                })
            },
            duration: duration,
            easing: 'linear',
            complete: function () {
                $current_tab.removeClass('current-tab');
                $next_tab.addClass('current-tab');
                $modal.animate({opacity:1}, {
                    step: function (now) {
                        let position = (1 - now) * offset;
                        $modal.css({
                            'transform': 'translateX(' + (direction === 'next' ? '' : '-') + position + 'px)'
                        })
                    },
                    duration: duration,
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
                console.log(result)
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

// Checks the form tab data-requirements array against the current form values
function check_requirements($next_tab) {
    let formValues = $('form.kudos_form').find(':input').serializeArray();
    let requirements = $next_tab.data('requirements');
    let result = true;
    if(requirements) {
        result = false;
        $next_tab.find(':input').attr('disabled', 'disabled');
        for(const[key,value] of Object.entries(requirements)) {
            formValues.filter(function (item) {
                if (item.name === key && value.includes(item.value)) {
                    result = true;
                    $next_tab.find(':input').removeAttr('disabled');
                }
            })
        }
    }
    return result;
}