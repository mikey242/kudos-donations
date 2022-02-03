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
    register,
    setFocus,
    formState: { errors }
  } = useFormContext()

  useEffect(() => {
    setFocus('value')
  }, [setFocus])

  return (
        <FormTab title={title} description={description}>
            <InputControl {...register('value', { required: true, min: 1, max: 5000 })}
                          type="number" placeholder={`${__('Amount', 'kudos-donations')} (€)`}/>
            {errors.value && <small className="error">{__('Minimum donation is 1 euro', 'kudos-donations')}</small>}

            <InputControl {...register('name', { required: true })}
                          placeholder={__('Name', 'kudos-donations')}/>
            {errors.name && <small className="error">{__('Your name is required', 'kudos-donations')}</small>}

            <InputControl {...register('email', { required: true })}
                          type="email" placeholder={__('Email', 'kudos-donations')}/>
            {errors.email && <small className="error">{__('Your email is required', 'kudos-donations')}</small>}

            <RadioControl {...register('payment_frequency', { required: true })} options={[
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
