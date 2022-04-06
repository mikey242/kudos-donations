import { __ } from '@wordpress/i18n'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import { useEffect } from '@wordpress/element'
import { TextControl, ToggleControl, RadioGroupControl } from '../../../common/components/controls'

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
                    fixedAmounts.map((value) => {
                      return { value: value, label: '€' + value }
                    })
                }/>
            }

            {(amountType === 'both' || amountType === 'open') &&
                <TextControl name="value"
                             addOn="€"
                             validation={{
                               required: __('Minimum donation is 1 euro', 'kudos-donations'),
                               min: { value: 1, message: __('Minimum donation is 1 euro', 'kudos-donations') },
                               max: { value: 5000, message: __('Maximum donation is 5000 euros', 'kudos-donations') }
                             }}
                             type="number" placeholder={
                    `${amountType === 'both' ? __('Other amount', 'kudos-donations') : __('Amount', 'kudos-donations')}`
                }/>
            }

            <TextControl name="name"
                         validation={{ required: __('Your name is required', 'kudos-donations') }}
                         placeholder={__('Name', 'kudos-donations')}/>

            <TextControl name="email_address"
                         validation={{ required: __('Your email is required', 'kudos-donations') }}
                         type="email" placeholder={__('Email', 'kudos-donations')}/>

            {donationType === 'both' &&
                <div className="flex justify-center mt-3">
                    <ToggleControl name="recurring" validation={{ required: true }}
                                   label={__('Recurring donation')}/>
                </div>
            }

        </FormTab>
  )
}

export default Initial
