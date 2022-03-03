import { __ } from '@wordpress/i18n'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import { useEffect } from '@wordpress/element'
import InputControl from '../controls/InputControl'
import ToggleControl from '../controls/ToggleControl'
import RadioGroupControl from '../controls/RadioGroupControl'

function Initial (props) {
  const { title, description } = props

  const {
    setFocus
  } = useFormContext()

  useEffect(() => {
    setFocus('value')
  }, [setFocus])

  return (
        <FormTab title={title} description={description}>

            <RadioGroupControl name="value" options={[
              { value: '12', label: __('12 Euros', 'kudos-donations') },
              { value: '3', label: __('3 Euros', 'kudos-donations') },
              { value: '1', label: __('1 Euro', 'kudos-donations') }
            ]}/>
            <InputControl name="value" error={__('Minimum donation is 1 euro', 'kudos-donations')}
                          validation={{ required: true, min: 1, max: 5000 }}
                          type="number" placeholder={`${__('Amount', 'kudos-donations')} (€)`}/>

            <InputControl name="name" error={__('Your name is required', 'kudos-donations')}
                          validation={{ required: true }}
                          placeholder={__('Name', 'kudos-donations')}/>

            <InputControl name="email_address" error={__('Your email is required', 'kudos-donations')}
                          validation={{ required: true }}
                          type="email" placeholder={__('Email', 'kudos-donations')}/>
            <ToggleControl name="payment_frequency" validation={{ required: true }}
                           label={__('Recurring donation')}/>
        </FormTab>
  )
}

export default Initial
