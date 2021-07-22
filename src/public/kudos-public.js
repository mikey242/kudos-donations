import _ from 'lodash'
import axios from 'axios'
import MicroModal from 'micromodal'
import 'jquery-validation'
import '../images/logo-colour-40.png' // used as email attachment
import '../images/logo-colour.svg'
import {getStyle, isVisible} from "../helpers/util"
import {handleMessages} from "../helpers/modal"
import {__} from "@wordpress/i18n"

import {
    animateInView,
    animateProgressBar,
    checkRequirements,
    createSummary,
    resetForm,
    resetProgressBar,
    valueChange
} from "../helpers/form"

jQuery(document).ready(($) => {

    'use strict'
    let screenSize

    $(() => {

        const kudosButtons = document.querySelectorAll('[data-kudos-target]')
        const kudosForms = document.querySelectorAll('form.kudos-form')
        let animating = false

        // Set screen size on load
        screenSize = getStyle('--kudos-screen')

        // Update screen size on window resize
        window.addEventListener('resize', _.debounce(() => {
            screenSize = getStyle('--kudos-screen')
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
        $.validator.messages.required = __('This field is required', 'kudos-donations')

        if (kudosButtons.length) {

            // Setup button action
            kudosButtons.forEach((e) => {

                const target = e.dataset.kudosTarget

                e.addEventListener('click', () => {
                    if (target) {
                        MicroModal.show(target, {
                            onShow(modal) {

                                const form = modal.querySelector('.kudos-form')

                                // Create and dispatch event
                                window.dispatchEvent(new CustomEvent('kudosShowModal', {detail: modal}))

                                // Animate progress bar
                                animateProgressBar(form)

                                // Clear error message
                                form.querySelector('.kudos_error_message').innerHTML = ""

                                // Reset and config form
                                if (form.length) {

                                    modal.querySelector('.kudos-modal-container').dataset.currentTab = 'initial'
                                    resetForm(form)
                                    $(form).validate().resetForm()

                                    // Set first input as focus
                                    if ('xs' !== screenSize) {
                                        form.querySelector('input[name="value"]').focus()
                                    }
                                }
                            },
                            onClose(modal) {
                                setTimeout(() => {
                                    resetProgressBar(modal)
                                }, 300)
                                // Create and dispatch event
                                window.dispatchEvent(new CustomEvent('kudosCloseModal', {detail: modal}))
                            },
                            awaitOpenAnimation: true,
                            awaitCloseAnimation: true,
                        })
                    }
                })
            })
        }

        // Configure forms.
        if (kudosForms) {
            // On scroll animate each progress-bar when visible.
            document.addEventListener('scroll', _.debounce(() => {
                kudosForms.forEach((form) => {
                    animateInView(form)
                })
            }, 100))

            // Attach listeners and set initial state.
            kudosForms.forEach((form) => {
                if (isVisible(form)) {
                    form.classList.add('kd-progress-animated')
                    animateProgressBar(form)
                }
                resetForm(form)
                valueChange(form)
            })
        }

        // Show message modal if exists.
        let messages = document.querySelectorAll('.kudos-message-modal')
        if (messages.length) {
            handleMessages(Array.from(messages))
        }

        // Hide honeypot field.
        document.querySelectorAll('input[name="donation"]').forEach((e) => {
            e.closest('label').classList.add('kd-hidden')
        })

        // Multi step form navigation.
        document.querySelectorAll('.kudos-form [data-direction]').forEach((button) => {
            button.addEventListener('click', (e) => {

                // Stop if already busy swapping tabs.
                if (animating) return false

                // Cache selectors.
                const currentTab = button.closest('.form-tab')
                const inputs = currentTab.elements

                // Check direction.
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

                $(e.currentTarget).validate()
                if ($(e.currentTarget).valid()) {

                    const error = form.querySelector('.kudos_error_message')
                    const formData = new FormData(e.target)
                    const timestamp = e.target.dataset.ts
                    formData.append('timestamp', timestamp);

                    form.classList.add('kd-is-loading')
                    error.classList.add('kd-hidden')

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
                            error.classList.remove('kd-hidden')
                            form.classList.add('error')
                            form.classList.remove('kd-is-loading')
                        }
                    })
                }
            })
        })

    })

    function changeTab(currentTab, direction, callback) {

        let container = currentTab.closest('.kudos-modal-container') ?? currentTab.closest('form')

        // Get element to animate
        let animate = container
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
        if ('FIELDSET' !== targetTab.nextElementSibling.tagName) {
            createSummary(currentTab.closest('form').id)
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
                    container.dataset.currentTab = targetTab.dataset.name

                    // Select first input on tab.
                    if ('xs' !== screenSize) {
                        let first = targetTab.elements[0]
                        first.focus()
                    }

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

})