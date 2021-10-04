import party from "party-js"
import {isVisible} from "./util"
import {__} from "@wordpress/i18n"

export function resetForm(form) {

    setFormHeight(form)

    // Switch back to first tab
    form.querySelectorAll('fieldset').forEach((e) => {
        e.classList.add('kd-hidden')
    })
    form.querySelector('fieldset').classList.remove('kd-hidden')

    // Reset amounts
    let amountInput = form.querySelector('[id^=value-open-both-]')
    let amountRadios = form.querySelectorAll('[id^=value-fixed-]')

    if (amountInput && amountRadios) {
        toggleAmount(amountInput, amountRadios)
        amountRadios[0].checked = true
        amountInput.removeAttribute('required')
        amountInput.setAttribute('name', '')
    }

    // Clear all form values
    form.reset()
}

// Set form height according to highest tab.
export function setFormHeight(form) {
    if (!form.closest('.kudos-modal')) {
        let tabs = form.querySelectorAll('fieldset')
        let array = Array.from(tabs).map(tab => {
            return tab.offsetHeight
        })
        let max = Math.max.apply(Math, array)

        form.style.minHeight = max + 'px'
    }
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
export function animateProgressBar(form) {

    let progressBar = form.querySelector('.kudos-campaign-progress')

    if (progressBar) {

        let goal = parseFloat(progressBar.dataset.goal)
        let total = parseFloat(progressBar.dataset.total)
        let percent = Math.round((total / goal) * 100)
        let bar = progressBar.querySelector('.kudos-progress-bar')
        let barInner = progressBar.querySelector('.kudos-progress-inner')
        let text = barInner.nextElementSibling

        barInner.classList.add('kd-scale-x-0')
        barInner.classList.remove('kd-scale-x-100')

        // Limit percentage to 100
        percent = percent > 100 ? 100 : percent

        // Set bar width a minimum of 10 percent if more than 0
        if (percent > 0) {
            bar.style.width = Math.max(percent, 10) + '%'
        }

        // Animate bar after after 500ms followed by adding text in another 500ms
        setTimeout(() => {
            barInner.classList.remove('kd-scale-x-0')
            barInner.classList.add('kd-scale-x-100')
            setTimeout(() => {
                text.innerHTML = percent + '% (€' + total + ')'
                text.style.opacity = '1'
                form.dispatchEvent(new Event('change'))
                bar.classList.add('kd-progress-animated')
                if (100 <= percent) {
                    party.confetti(form.querySelector('.kudos-progress-total'))
                }
            }, 1000)
        }, 500)

        valueChange(form)
    }
}

// Check that a value field has changed
export function valueChange(form) {

    form.addEventListener('change', (e) => {

        let progressBar = form.querySelector('.kudos-campaign-progress')
        if(progressBar) {
            let goal = parseFloat(progressBar.dataset.goal)
            let total = parseFloat(progressBar.dataset.total)

            let extra = form.querySelector('.kudos-campaign-progress').querySelector('.kudos-progress-extra')
            if ("value" === e.target.name) {
                let value = Number.isInteger(parseFloat(form.value.value)) ? parseFloat(form.value.value) : 0
                let newPercent = value / (goal - total) * 100
                // Limit percentage to 100
                newPercent = newPercent > 100 ? 100 : newPercent
                extra.style.transform = 'scaleX(' + newPercent + '%)'
                if (100 <= newPercent) {
                    party.confetti(form.querySelector('.kudos-progress-total'))
                }
            }
        }
    })
}

// Resets the progress bar values of the supplied modal
export function resetProgressBar(form) {

    let progressBar = form.querySelector('.kudos-campaign-progress')

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

export function animateInView(form) {
    if (!form.classList.contains('kd-progress-animated')) {
        if (isVisible(form)) {
            form.classList.add('kd-progress-animated')
            animateProgressBar(form)
        } else {
            resetProgressBar(form)
        }
    }
}