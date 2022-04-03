import { __ } from '@wordpress/i18n'
import React from 'react'
import FormTab from './FormTab'
import { useFormContext } from 'react-hook-form'
import { getFrequencyName } from '../../../common/helpers/form'
import CheckboxControl from '../../../common/components/controls/CheckboxControl'

function Summary (props) {
  const { title, description, privacyLink, termsLink } = props

  const {
    getValues
  } = useFormContext()

  const recurringText = () => {
    const recurring = getValues('recurring')
    if (!recurring) return __('One off')
    const recurringFrequency = getFrequencyName(getValues('recurring_frequency'))
    const recurringLength = getValues('recurring_length')
    const length = recurringLength > 0 ? recurringLength + ' ' + __('years') : __('Continuous')
    return `${__('Recurring')} (${recurringFrequency} / ${length})`
  }

  return (
        <FormTab title={title} description={description}>
            <div
                className="kudos_summary text-left block bg-gray-100 p-2 border-0 border-solid border-t-2 border-primary">
                <p className="my-1">
                    <strong>{__('Name', 'kudos-donations')}: </strong><span>{getValues('name')}</span>
                </p>
                <p className="my-1">
                    <strong>{__('E-mail address', 'kudos-donations')} : </strong><span>{getValues('email')}</span>
                </p>
                <p className="my-1">
                    <strong>{__('Amount', 'kudos-donations')}: </strong>€<span>{getValues('value')}</span>
                </p>
                <p className="my-1">
                    <strong>{__('Type', 'kudos-donations')}: </strong><span>{recurringText()}</span>
                </p>
            </div>
            {privacyLink && <CheckboxControl name="privacy" label={__('Accept privacy policy')}/>}
            {termsLink && <CheckboxControl name="terms" label={__('Accept Terms and Conditions')}/>}
        </FormTab>
  )
}

export default Summary
