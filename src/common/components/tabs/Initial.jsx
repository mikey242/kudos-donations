import { __ } from '@wordpress/i18n'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import { useEffect } from '@wordpress/element'
import InputControl from '../controls/InputControl'
import ToggleControl from '../controls/ToggleControl'
import RadioGroupControl from '../controls/RadioGroupControl'

const Initial = (props) => {
  const { title, description, buttons, donationType, amountType, fixedAmounts } = props

  const {
    setValue
  } = useFormContext()

  useEffect(() => {
    if (donationType !== 'both') {
      setValue('recurring', donationType === 'recurring')
    }
  })

  return (
        <FormTab
            title={title}
            description={description}
            buttons={buttons}
        >

            {(amountType === 'both' || amountType === 'fixed') &&
                <RadioGroupControl name="value" options={
                    fixedAmounts.split(',').map((value) => {
                      return { value: value, label: '€' + value }
                    })
                }/>
            }

            {(amountType === 'both' || amountType === 'open') &&
                <InputControl name="value"
                              validation={{
                                required: __('Minimum donation is 1 euro', 'kudos-donations'),
                                min: { value: 1, message: __('Minimum donation is 1 euro', 'kudos-donations') },
                                max: { value: 5000, message: __('Maximum donation is 5000 euros', 'kudos-donations') }
                              }}
                              type="number" placeholder={
                    `${amountType === 'both' ? __('Other amount', 'kudos-donations') : __('Amount', 'kudos-donations')} (€)`
                }/>
            }

            <InputControl name="name"
                          validation={{ required: __('Your name is required', 'kudos-donations') }}
                          placeholder={__('Name', 'kudos-donations')}/>

            <InputControl name="email_address"
                          validation={{ required: __('Your email is required', 'kudos-donations') }}
                          type="email" placeholder={__('Email', 'kudos-donations')}/>

            {donationType === 'both' &&
                <ToggleControl name="recurring" validation={{ required: true }}
                               label={__('Recurring donation')}/>
            }

        </FormTab>
  )
}

export default Initial
