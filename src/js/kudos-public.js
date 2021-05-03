import axios from 'axios'
import MicroModal from 'micromodal'
import 'jquery-validation'
import '../img/logo-colour-40.png' // used as email attachment
import '../img/logo-colour.svg' // used for logo on modal

jQuery(document).ready(($) => {

    'use strict'
    const {__} = wp.i18n

    $(() => {

        const $kudosButtons = $('.kudos_button_donate')
        let animating = false

        // Custom validation method to check subscription
        $.validator.addMethod('totalPayments', (value, element) => {
            let form = element.closest('form')
            let frequency = $(form).find('[name="recurring_frequency"]').val()
            if (frequency) {
                return 12 / parseInt(frequency) * value !== 1
            }
            return true
        })

        // Set validation defaults
        $.validator.setDefaults({
            ignore: [],
            errorElement: 'small',
            onfocusout: false,
            errorPlacement: (error, element) => {
                if (element.attr("type") === 'checkbox' || element.hasClass('kd-input-group-input')) {
                    error.insertAfter(element.parent())
                } else {

                    error.appendTo(element.closest('label'))
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
                terms: {
                    required: __('You must agree to our terms and conditions before donating', 'kudos-donations')
                },
                privacy: {
                    required: __('You must agree to our privacy policy before donating', 'kudos-donations')
                }
            },
        })
        $.validator.messages.required = __("This field is required", 'kudos-donations')

        if ($kudosButtons.length) {

            // Setup button action
            $kudosButtons.each(function () {

                const target = $(this).data('target')

                $(this).on('click', function () {
                    if (target) {
                        MicroModal.show(target, {
                            onShow(modal) {

                                // Create and dispatch event
                                const modalEvent = new CustomEvent('kudosShowModal', {detail: modal})
                                window.dispatchEvent(modalEvent)

                                // Clear error message
                                $(modal)
                                    .find('.kudos_error_message')
                                    .text('')

                                // Reset and config form
                                const $form = $(modal).find('.kudos_form')
                                if ($form.length) {

                                    // Switch back to first tab
                                    $form.find('fieldset').addClass(
                                        'kd-hidden'
                                    )
                                    $('fieldset:first-child').removeClass(
                                        'kd-hidden'
                                    )

                                    // Reset amounts
                                    let $amountInput = $form.find('[id^=amount-open-kudos_modal]')
                                    let $amountRadios = $form.find('[id^=amount-fixed-kudos_modal]')
                                    toggleAmount($amountInput, $amountRadios)
                                    $($amountRadios[0]).prop('checked', true)
                                    $amountInput.attr({'required': false, 'name': ''})

                                    // Clear all values
                                    $form.validate().resetForm()
                                    $form[0].reset()

                                    // Set first input as focus
                                    $form.find('input[name="value"]:first').trigger('focus')
                                }
                            },
                            onClose(modal) {
                                // Create and dispatch event
                                const modalEvent = new CustomEvent('kudosCloseModal', {detail: modal})
                                window.dispatchEvent(modalEvent)
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

        // Hide field
        $('input[name="donation"]').each(function () {
            $(this).closest('label').css('display', 'none')
        })

        // Multi step form navigation
        $('.kudos_form [data-direction]').on('click', function () {
            if (animating) return false
            // Cache selectors
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
                $next_tab = direction === 'next' ? $next_tab.next() : $next_tab.prev()
                change = checkRequirements($next_tab)
            }

            if ($next_tab.hasClass('form-tab-final')) {
                let id = $(this).closest('.kudos-modal').attr('id')
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
                        let page = $modal.attr('data-page')
                        let newPage = (direction === 'next' ? +page + 1 : +page - 1)
                        $modal.attr('data-page', newPage)
                        $current_tab.addClass('kd-hidden')
                        $next_tab.removeClass('kd-hidden')
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

        // Submit donation form action
        document.querySelectorAll('form.kudos_form').forEach((form) => {

            form.addEventListener('submit', (e) => {
                e.preventDefault()

                $(e.currentTarget).validate()
                if ($(e.currentTarget).valid()) {

                    const modal = form.closest('.kudos_form_modal')
                    const error = modal.querySelector('.kudos_error_message')
                    const formData = new FormData(e.target)

                    modal.classList.add('kd-is-loading')

                    axios.post(kudos.createPaymentUrl, JSON.stringify(Object.fromEntries(formData)), {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': kudos._wpnonce
                        }
                    }).then((result) => {
                        if (result.data.success) {
                            window.location.href = result.data.data
                        } else {
                            error.innerHTML = result.data.data.message
                            modal.classList.add('error')
                            modal.classList.remove('kd-is-loading')
                        }
                    })
                }
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
                        $nextTab.find(':input').attr('disabled', false)
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

    // Set input attributes when 'both' amount type is used
    function toggleAmount($amountInput, $amountRadios) {

        $amountInput.on('input', function () {
            $amountInput.attr({'required': true, 'name': 'value'})
            $amountRadios.each(function (i, e) {
                $(e).prop('checked', false)
                $(e).attr({'name': ''})
            })
        })

        $amountRadios.each(function (i, e) {
            $(e).on('change', function () {
                $(e).attr({'name': 'value'})
                $amountInput.attr({'required': false, 'name': ''})
                $amountInput.valid()
                $amountInput.val('')
            })
        })
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

})