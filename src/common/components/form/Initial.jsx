import { __ } from '@wordpress/i18n'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import { useEffect } from '@wordpress/element'
import InputControl from '../controls/InputControl'
import ToggleControl from '../controls/ToggleControl'
import RadioGroupControl from '../controls/RadioGroupControl'

const Initial = (props) => {
  const { title, description, donationType, amountType, fixedAmounts } = props

  const {
    setValue
  } = useFormContext()

  useEffect(() => {
    if (donationType !== 'both') {
      setValue('recurring', donationType === 'recurring')
    }
  })

  return (
        <FormTab title={title} description={description}>

            {(amountType === 'both' || amountType === 'fixed') &&
                <RadioGroupControl name="value" options={
                    fixedAmounts.split(',').map((value) => {
                      return { value: value, label: '€' + value }
                    })
                }/>
            }

            {(amountType === 'both' || amountType === 'open') &&
                <InputControl name="value" error={__('Minimum donation is 1 euro', 'kudos-donations')}
                              validation={{ required: true, min: 1, max: 5000 }}
                              type="number" placeholder={
                    `${amountType === 'both' ? __('Other amount', 'kudos-donations') : __('Amount', 'kudos-donations')} (€)`
                }/>
            }

            <InputControl name="name" error={__('Your name is required', 'kudos-donations')}
                          validation={{ required: true }}
                          placeholder={__('Name', 'kudos-donations')}/>

            <InputControl name="email_address" error={__('Your email is required', 'kudos-donations')}
                          validation={{ required: true }}
                          type="email" placeholder={__('Email', 'kudos-donations')}/>

            {donationType === 'both' &&
                <ToggleControl name="recurring" validation={{ required: true }}
                               label={__('Recurring donation')}/>
            }

        </FormTab>
  )
}

export default Initial
