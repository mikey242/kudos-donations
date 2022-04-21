import { __ } from '@wordpress/i18n'
import React from 'react'
import FormTab from './FormTab'
import { useFormContext } from 'react-hook-form'
import { getFrequencyName } from '../../../common/helpers/form'
import { CheckboxControl } from '../../../common/components/controls'

function Summary (props) {
  const { title, description, privacyLink, termsLink } = props

  const {
    getValues
  } = useFormContext()

  const recurringText = () => {
    const recurring = getValues('recurring')
    if (!recurring) return __('One off', 'kudos-donations')
    const recurringFrequency = getFrequencyName(getValues('recurring_frequency'))
    const recurringLength = getValues('recurring_length')
    const length = recurringLength > 0 ? recurringLength + ' ' + __('years', 'kudos-donations') : __('Continuous', 'kudos-donations')
    return `${__('Recurring', 'kudos-donations')} (${recurringFrequency} / ${length})`
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
            {privacyLink && <CheckboxControl name="privacy" label={__('Accept privacy policy', 'kudos-donations')}
                                             validation={{ required: __('Please accept this to continue', 'kudos-donations') }}/>}
            {termsLink && <CheckboxControl name="terms" label={__('Accept Terms and Conditions', 'kudos-donations')}
                                           validation={{ required: __('Please accept this to continue', 'kudos-donations') }}/>}
        </FormTab>
  )
}

export default Summary
