import _ from 'lodash'
import axios from 'axios'
import MicroModal from 'micromodal'
import 'jquery-validation'
import '../img/logo-colour-40.png' // used as email attachment
import '../img/logo-colour.svg'

jQuery(document).ready(($) => {

    'use strict'
    const {__} = wp.i18n
    let screenSize

    $(() => {

        const $kudosButtons = $('.kudos_button_donate')
        let animating = false

        // Set screen size on load
        updateScreenSize()

        // Update screen size on window resize
        $(window).on('resize', _.debounce(function () {
            updateScreenSize()
        }, 100))

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
                error.insertAfter(element.parent())
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
                                window.dispatchEvent(
                                    new CustomEvent('kudosShowModal', {detail: modal})
                                )

                                // Clear error message
                                $(modal)
                                    .find('.kudos_error_message')
                                    .text('')

                                // Reset and config form
                                const $form = $(modal).find('.kudos-form')
                                if ($form.length) {

                                    // Switch back to first tab
                                    $form.find('fieldset').addClass(
                                        'kd-hidden'
                                    )
                                    $form.find('fieldset').first().removeClass(
                                        'kd-hidden'
                                    )

                                    // Reset amounts
                                    let $amountInput = $form.find('[id^=value-open-both-]')
                                    let $amountRadios = $form.find('[id^=value-fixed-]')
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
                            awaitOpenAnimation: true,
                            awaitCloseAnimation: true,
                        })
                    }
                })
            })
        }

        // Show message modal if exists
        let messages = $('.kudos-message-modal').toArray()
        if (messages.length) {
            handleMessages(messages)
        }

        // Hide field
        $('input[name="donation"]').each(function () {
            $(this).closest('label').css('display', 'none')
        })

        // Multi step form navigation
        document.querySelectorAll('.kudos-form [data-direction]').forEach((button) => {
            button.addEventListener('click', (e) => {

                // Stop if already busy swapping tabs
                if (animating) return false

                // Cache selectors
                const currentTab = button.closest('.form-tab')
                const inputs = currentTab.elements

                // Check direction
                const direction = button.dataset.direction
                if ('next' === direction) {
                    $(inputs).validate()
                    if (!$(inputs).valid()) {
                        return false
                    }
                }

                changeTab(currentTab, direction, () => {
                    animating = false
                })
            })
        })

        // Submit donation form action.
        document.querySelectorAll('form.kudos-form').forEach((form) => {

            // Prevent form submit on enter.
            form.onkeydown = function (e) {
                if (13 === e.keyCode) {
                    e.preventDefault()
                    $(e.target).closest('fieldset').find('[data-direction="next"]').click()
                }
            }

            form.addEventListener('submit', (e) => {

                e.preventDefault()

                console.log($(e.currentTarget).valid())

                $(e.currentTarget).validate()
                if ($(e.currentTarget).valid()) {

                    const modal = form.closest('.kudos-modal')
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

    function changeTab(currentTab, direction, callback) {

        // Get element to animate
        let animate = currentTab.closest('.kudos-modal-container')
        if ('xs' === screenSize) {
            animate = currentTab.closest('form')
        }

        // Calculate direction
        let targetTab = currentTab
        let change = false
        while (!change) {
            targetTab = direction === 'next' ? targetTab.nextElementSibling : targetTab.previousElementSibling
            change = checkRequirements(targetTab)
        }

        // Show summary if next tab is final
        if (null === targetTab.nextElementSibling) {
            createSummary('#' + currentTab.closest('.kudos-modal').id)
        }

        const offset = 25
        const duration = 150
        $(animate).animate(
            {opacity: 0},
            {
                step(now) {
                    const position = (1 - now) * offset
                    $(animate).css({
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
                    // Prepare tab props.
                    currentTab.classList.add('kd-hidden')
                    targetTab.classList.remove('kd-hidden')

                    // Select first input on tab.
                    let first = targetTab.elements[0]
                    first.focus()

                    // Begin animating.
                    $(animate).animate(
                        {opacity: 1},
                        {
                            step(now) {
                                const position = (1 - now) * offset
                                $(animate).css({
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
                                callback()
                            },
                        }
                    )
                },
            }
        )
    }

    // Checks the form tab data-requirements array against the current form values
    function checkRequirements(targetTab) {

        // Cache selectors
        const form = targetTab.closest('form')

        // Get form data and next tab requirements
        const formData = new FormData(form)
        const requirements = targetTab.dataset.requirements
        const inputs = targetTab.elements

        // Check if next tab meets requirements
        let result = true
        if (requirements) {
            result = false
            // Disabled all inputs to prevent submission
            for (let i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute("disabled", "")
            }
            for (const [reqName, reqValue] of Object.entries(JSON.parse(requirements))) {
                for (const [inputName, inputValue] of formData.entries()) {
                    if (inputName === reqName && reqValue.includes(inputValue)) {
                        result = true
                        // Re-enable inputs if tab used
                        for (let i = 0; i < inputs.length; i++) {
                            inputs[i].removeAttribute("disabled", false)
                        }
                    }
                }
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

    function updateScreenSize() {
        screenSize = getComputedStyle(document.documentElement).getPropertyValue('--kudos-screen')
    }

})