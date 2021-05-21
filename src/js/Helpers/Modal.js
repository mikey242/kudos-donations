import MicroModal from "micromodal"
import party from 'party-js'

const {__} = wp.i18n

// Handles the messages by showing the modals in order
export function handleMessages(messages) {

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

// Set input attributes when 'both' amount type is used
export function toggleAmount(amountInput, amountRadios) {

    amountInput.addEventListener('input', function () {
        amountInput.setAttribute('required', true)
        amountInput.setAttribute('name', 'value')
        amountRadios.forEach((e) => {
            e.setAttribute('name', '')
            e.checked = false
        })
    })

    amountRadios.forEach((e) => {
        e.addEventListener('change', () => {
            e.setAttribute('name', 'value')
            amountInput.removeAttribute("required")
            amountInput.setAttribute('name', '')
            amountInput.value = ''
        })
    })
}

// Create the summary at the end of the form before submitting
export function createSummary(formId) {
    const form = document.getElementById(formId)
    const formData = new FormData(form)
    const name = formData.get('name') || ''
    const email = formData.get('email_address') || ''
    const value = formData.get('value') || ''
    const frequency = formData.get('payment_frequency')
    let type

    if (frequency === 'recurring') {
        const recurring_frequency = formData.get('recurring_frequency')
        const recurring_length = formData.get('recurring_length')
        const length = form.querySelector("[name='recurring_length'] option[value='" + recurring_length + "']").innerHTML
        const frequency = form.querySelector("[name='recurring_frequency'] option[value='" + recurring_frequency + "']").innerHTML
        type = __('Recurring', 'kudos-donations') + " ( " + frequency + ' / ' + length + " )"
    } else {
        type = __('One-off', 'kudos-donations')
    }

    form.querySelector('.summary_name').innerHTML = name
    form.querySelector('.summary_email').innerHTML = email
    form.querySelector('.summary_value').innerHTML = value
    form.querySelector('.summary_frequency').innerHTML = type
}

// Checks the form tab data-requirements array against the current form values
export function checkRequirements(targetTab) {

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

// Animates the progress bar, if found, for the supplies modal
export function animateProgressBar(modal) {

    let progressBar = modal.querySelector('.kudos-campaign-progress')

    if (progressBar) {

        let form = modal.querySelector('form')
        let goal = parseFloat(progressBar.dataset.goal)
        let total = parseFloat(progressBar.dataset.total)
        let percent = Math.round((total / goal) * 100)
        let bar = progressBar.querySelector('.kudos-progress-bar')
        let extra = progressBar.querySelector('.kudos-progress-extra')
        let barInner = progressBar.querySelector('.kudos-progress-inner')
        let text = barInner.nextElementSibling

        // Limit percentage to 100
        percent = percent > 100 ? 100 : percent

        // Set bar width a minimum of 10 percent
        bar.style.width = Math.max(percent, 10) + '%'

        // Animate bar after after 500ms followed by adding text in another 500ms
        setTimeout(() => {
            barInner.classList.remove('kd-scale-x-0')
            barInner.classList.add('kd-scale-x-100')
            setTimeout(() => {
                text.innerHTML = percent + '%'
                text.style.opacity = '1'
                form.dispatchEvent(new Event('change'))
                confetti(percent)
            }, 1000)
        }, 500)

        form.addEventListener('change', (e) => valueChange(e))

        function valueChange(e) {
            // Check that a value field has changed
            if ("value" === e.target.name) {
                let value = Number.isInteger(parseFloat(form.value.value)) ? parseFloat(form.value.value) : 0
                let newPercent = Math.round(value / (goal - total) * 100)
                // Limit percentage to 100
                newPercent = newPercent > 100 ? 100 : newPercent
                extra.style.transform = 'scaleX(' + newPercent + '%)'
                confetti(newPercent)
            }
        }

        // Check if supplied value is > 100 and if so confetti!
        function confetti(value) {
            if (100 <= value) {
                party.confetti(modal.querySelector('.kudos-progress-total'))
            }
        }
    }
}

// Resets the progress bar values of the supplied modal
export function resetProgressBar(modal) {

    let progressBar = modal.querySelector('.kudos-campaign-progress')

    if (progressBar) {
        // Cache selectors
        let barInner = progressBar.querySelector('.kudos-progress-inner')
        let extra = progressBar.querySelector('.kudos-progress-extra')
        let text = barInner.nextElementSibling

        // Reset classes and inline styles
        barInner.classList.remove('kd-scale-x-100')
        barInner.classList.add('kd-scale-x-0')
        extra.style.transform = 'scaleX(0)'
        text.innerHTML = ''
        text.style.opacity = '0'
    }
}