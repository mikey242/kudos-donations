import { __ } from '@wordpress/i18n'
import React from 'react'
import FormTab from './FormTab'
import { useFormContext } from 'react-hook-form'
import { getFrequencyName } from '../../helpers/form'

function Summary (props) {
  const { title, description } = props

  const {
    getValues
  } = useFormContext()

  const recurringText = () => {
    const type = getValues('payment_frequency')
    if (type === 'oneoff') return __('One off')
    const recurringFrequency = getFrequencyName(getValues('recurring_frequency'))
    const recurringLength = getValues('recurring_length')
    const length = recurringLength > 0 ? recurringLength + ' ' + __('years') : __('Continuous')
    return `${__('Recurring')} (${recurringFrequency} / ${length})`
  }

  return (
        <FormTab title={title} description={description}>
            <div
                className="kudos_summary text-left block bg-gray-100 p-2 border-0 border-solid border-t-2 border-primary">
                <p className="m-0"><strong>{__('Name', 'kudos-donations')}: </strong><span
                >{getValues('name')}</span></p>
                <p className="m-0"><strong>{__('E-mail address', 'kudos-donations')}
                    : </strong><span
                >{getValues('email')}</span></p>
                <p className="m-0"><strong>{__('Amount', 'kudos-donations')}: </strong>€<span
                >{getValues('value')}</span></p>
                <p className="m-0"><strong>{__('Type', 'kudos-donations')}: </strong><span>{
                    recurringText()
                }</span>
                </p>
            </div>
        </FormTab>
  )
}

export default Summary
