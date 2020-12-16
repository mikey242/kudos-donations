import $ from 'jquery'
import MicroModal from 'micromodal'
import {dom, library} from '@fortawesome/fontawesome-svg-core'
import {faChevronLeft} from '@fortawesome/free-solid-svg-icons'
import 'jquery-validation'
import '../img/logo-colour-40.png' //used as email attachment
const {__} = wp.i18n
library.add(faChevronLeft)
dom.watch()

$(() => {
    'use strict'

    const $body = $('body')
    const $kudosButtons = $('.kudos_button_donate')
    const $textValue = $('#value_open')
    let $textLabel = $('#value_open + label')
    const $radioValues = $('input[type="radio"][name="value"]')
    let animating = false

    // Custom validation method to check subscription
    $.validator.addMethod('totalPayments', (value, element) => {
        let frequency = $('[name="recurring_frequency"]').val()
        if (frequency) {
            return 12 / parseInt(frequency) * value !== 1
        }
        return true
    })

    // Set validation defaults
    $.validator.setDefaults({
        ignore: ':disabled',
        errorElement: 'small',
        onfocusout: false,
        errorPlacement: (error, element) => {
            if (element.attr("type") === 'checkbox' || element.attr('id') === 'value_open') {
                error.insertAfter(element.parent())
            } else {
                error.insertAfter(element)
            }
        },
        rules: {
            value: {
                digits: true,
            },
            email_address: {
                email: true
            },
            recurring_length: {
                totalPayments: true
            }
        },
        messages: {
            name: {
                required: __('Your name is required', 'kudos-donations'),
            },
            email_address: {
                required: __('Your email is required', 'kudos-donations'),
                email: __('Please enter a valid email', 'kudos-donations'),
            },
            value: {
                required: __('Donation amount is required', 'kudos-donations'),
                min: __('Minimum donation is 1 euro', 'kudos-donations'),
                digits: __('Only digits are valid', 'kudos-donations'),
            },
            recurring_frequency: {
                required: __('Please select a payment frequency', 'kudos-donations')
            },
            recurring_length: {
                required: __('Please select a payment duration', 'kudos-donations'),
                totalPayments: __('Subscriptions must be more than one payment', 'kudos-donations')
            },
            avg: {
                required: __('You must agree to our privacy policy before donating', 'kudos-donations')
            }
        },
    })

    if ($kudosButtons.length) {

        // Set input attributes when 'both' amount type is used
        if($textValue.length) {

            $textValue.on('input', function () {
                $textValue.attr({'required': true, 'name': 'value'})
                $radioValues.each(function (i, e) {
                    $(e).removeAttr('checked')
                })
            })

            $radioValues.each(function (i, e) {
                $(e).on('change', function () {
                    $textValue.attr({'required': false, 'name': ''})
                    $textValue.valid()
                    $textValue.val('')
                })
            })
        }

        // Setup button action
        $kudosButtons.each(function () {
            $(this).click(function () {
                const $target = $(this).data('target')
                if ($target) {
                    MicroModal.show($target, {
                        onShow(modal) {
                            $(modal)
                                .find('.kudos_error_message')
                                .text('')
                            const $form = $(modal).find('.kudos_form')
                            if ($form.length) {
                                $('fieldset.current-tab').removeClass(
                                    'current-tab'
                                )
                                $('fieldset:first-child').addClass(
                                    'current-tab'
                                )
								$($radioValues[0]).attr('checked', true)
                                $textValue.attr({'required': false, 'name': ''})
                                $form.validate().resetForm()
                                $form[0].reset()
                            }
                        },
                        awaitCloseAnimation: true,
                    })
                }
            })
        })
    }

    // Show message modal if exists
    let messages = $('.kudos_message_modal').toArray()
    if (messages.length) {
        handleMessages(messages)
    }

    // Multi step form navigation
    $('.kudos_modal [data-direction]').on('click', function () {
        if (animating) return false
        const $current_tab = $(this).closest('.form-tab')
        const $modal = $(this).closest('.kudos_modal_container')
        const $inputs = $current_tab.find(':input')
        const direction = $(this).data('direction')

        // Validate fields before proceeding
        if (direction === 'next') {
            $inputs.validate()
            if (!$inputs.valid()) {
                return
            }
        }

        // Calculate next tab
        let $next_tab = $current_tab
        let change = false
        while (!change) {
            $next_tab =
                direction === 'next' ? $next_tab.next() : $next_tab.prev()
            change = checkRequirements($next_tab)
        }

        if ($next_tab.hasClass('form-tab-final')) {
            let id = $(this).closest('.kudos_modal').attr('id')
            createSummary('#' + id)
        }

        // Begin animation
        animating = true
        const offset = 25
        const duration = 150
        $modal.animate(
            {opacity: 0},
            {
                step(now) {
                    const position = (1 - now) * offset
                    $modal.css({
                        transform:
                            'translateX(' +
                            (direction === 'next' ? '-' : '') +
                            position +
                            'px)',
                    })
                },
                duration,
                easing: 'linear',
                complete() {
                    $current_tab.removeClass('current-tab')
                    $next_tab.addClass('current-tab')
                    $modal.animate(
                        {opacity: 1},
                        {
                            step(now) {
                                const position = (1 - now) * offset
                                $modal.css({
                                    transform:
                                        'translateX(' +
                                        (direction === 'next' ? '' : '-') +
                                        position +
                                        'px)',
                                })
                            },
                            duration,
                            easing: 'linear',
                            complete() {
                                animating = false
                            },
                        }
                    )
                },
            }
        )
    })

    // Check form before submit
    $body.on('click', '.kudos_submit', function (e) {
        e.preventDefault()
        const $form = $(this.form)
        $form.validate()
        if ($form.valid()) {
            $form.submit()
        }
    })

    // Submit donation form action
    $body.on('submit', 'form.kudos_form', function (e) {
        e.preventDefault()
        const $kudosFormModal = $(this).closest('.kudos_form_modal')
        const $kudosErrorMessage = $kudosFormModal.find(
            '.kudos_error_message'
        )

        $.ajax({
            method: 'post',
            dataType: 'json',
            url: kudos.ajaxurl,
            data: {
                action: 'submit_payment',
                form: $(e.currentTarget).serialize(),
            },
            beforeSend() {
                $kudosFormModal.addClass('kudos_loading')
            },
            success(result) {
                console.log(result)
                if (result.success) {
                    $(location).attr('href', result.data)
                } else {
                    $kudosErrorMessage.text(result.data.message)
                    $kudosFormModal
                        .removeClass('kudos_loading')
                        .addClass('error')
                }
            },
            error(error) {
                console.log('error', error)
            },
        })
    })
})

// Checks the form tab data-requirements array against the current form values
function checkRequirements($nextTab) {
    const formValues = $nextTab.closest('form.kudos_form').find(':input').serializeArray()
    const requirements = $nextTab.data('requirements')
    let result = true
    if (requirements) {
        result = false
        $nextTab.find(':input').attr('disabled', 'disabled')
        for (const [key, value] of Object.entries(requirements)) {
            formValues.filter(function (item) {
                if (item.name === key && value.includes(item.value)) {
                    result = true
                    $nextTab.find(':input').removeAttr('disabled')
                }
            })
        }
    }
    return result
}

// Create the summary at the end of the form before submitting
function createSummary(id) {
    const form = $(id).find('form')
    const values = form.serializeArray()
    const name = values.find((i) => i.name === 'name').value
    const email = values.find((i) => i.name === 'email_address').value
    const value = values.find((i) => i.name === 'value').value
    const frequency = values.find((i) => i.name === 'payment_frequency').value
    let type

    if (frequency === 'recurring') {
        const recurring_frequency = values.find((i) => i.name === 'recurring_frequency').value
        const recurring_length = values.find((i) => i.name === 'recurring_length').value
        const length = $(id + " [name='recurring_length'] option[value='" + recurring_length + "']")[0].text
        const frequency = $(id + " [name='recurring_frequency'] option[value='" + recurring_frequency + "']")[0].text
        type = __('Recurring', 'kudos-donations') + " ( " + frequency + ' / ' + length + " )"
    } else {
        type = __('One-off', 'kudos-donations')
    }

    $(id + ' ' + '.summary_name').text(name)
    $(id + ' ' + '.summary_email').text(email)
    $(id + ' ' + '.summary_value').text(value)
    $(id + ' ' + '.summary_frequency').text(type)
}

// Handles the messages by showing the modals in order
function handleMessages(messages) {

    let showMessage = () => {
        MicroModal.show(messages[0].id, {
            onClose: () => {
                messages.shift()
                if (messages.length) {
                    showMessage()
                }
            },
            awaitCloseAnimation: true,
            awaitOpenAnimation: true,
        })
    }

    showMessage()
}