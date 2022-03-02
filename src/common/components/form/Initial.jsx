import { __ } from '@wordpress/i18n'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import { useEffect } from '@wordpress/element'
import InputControl from './InputControl'
import RadioControl from './RadioControl'

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

            <InputControl name="value" error={__('Minimum donation is 1 euro', 'kudos-donations')}
                          validation={{ required: true, min: 1, max: 5000 }}
                          type="number" placeholder={`${__('Amount', 'kudos-donations')} (€)`}/>

            <InputControl name="name" error={__('Your name is required', 'kudos-donations')}
                          validation={{ required: true }}
                          placeholder={__('Name', 'kudos-donations')}/>

            <InputControl name="email_address" error={__('Your email is required', 'kudos-donations')}
                          validation={{ required: true }}
                          type="email" placeholder={__('Email', 'kudos-donations')}/>
            <RadioControl name="payment_frequency" validation={{ required: true }} options={[
              {
                label: __('One-off', 'kudos-donations'),
                value: 'oneoff'
              },
              {
                label: __('Recurring', 'kudos-donations'),
                value: 'recurring'
              }
            ]}/>
        </FormTab>
  )
}

export default Initial
