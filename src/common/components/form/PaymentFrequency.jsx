import { __ } from '@wordpress/i18n'
import { useFormContext } from 'react-hook-form'
import React from 'react'
import FormTab from './FormTab'

function PaymentFrequency (props) {
  const { title, description, getValues } = props

  const {
    register,
    formState: { errors }
  } = useFormContext()

  const isMoreThanOne = (years) => {
    const frequency = getValues('recurring_frequency')
    if (frequency) {
      return (12 / parseInt(frequency, 10)) * years !== 1
    }
    return true
  }

  return (
        <FormTab title={title} description={description}>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <select
                    {...register('recurring_frequency', { required: __('Please select a payment frequency', 'kudos-donations') })}
                    defaultValue=""
                    className="bg-select appearance-none bg-white bg-no-repeat bg-right-2 border-gray-300 border border-solid focus:border-primary focus:ring-primary transition-colors ease-in-out rounded w-full py-2 px-3 text-gray-700"
                >
                    <option value="" disabled>{__('Payment frequency', 'kudos-donations')}</option>
                    <option value="12 months">{__('Yearly', 'kudos-donations')}</option>
                    <option value=" 3 months">{__('Quarterly', 'kudos-donations')}</option>
                    <option value=" 1 month">{__('Monthly', 'kudos-donations')}</option>
                </select>
            </label>
            {errors?.recurring_frequency &&
                <small className="error">{errors?.recurring_frequency?.message}</small>}
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <select
                    {...register('recurring_length', {
                      required: __('Please select a payment duration', 'kudos-donations'),
                      validate: { isMoreThanOne: (v) => isMoreThanOne(v) || __('Subscriptions must be more than one payment', 'kudos-donations') }
                    })
                    }
                    defaultValue=""
                    className="bg-select appearance-none bg-white bg-no-repeat bg-right-2 border-gray-300 border border-solid focus:border-primary focus:ring-primary transition-colors ease-in-out rounded w-full py-2 px-3 text-gray-700"
                >
                    <option value="" disabled>{__('Donation duration', 'kudos-donations')}</option>
                    <option value="0">{__('Continuous', 'kudos-donations')}</option>
                    <option value="1">{__('1 year', 'kudos-donations')}</option>
                    <option value="2">{__('2 years', 'kudos-donations')}</option>
                    <option value="3">{__('3 years', 'kudos-donations')}</option>
                    <option value="4">{__('4 years', 'kudos-donations')}</option>
                    <option value="5">{__('5 years', 'kudos-donations')}</option>
                    <option value="6">{__('6 years', 'kudos-donations')}</option>
                    <option value="7">{__('7 years', 'kudos-donations')}</option>
                    <option value="8">{__('8 years', 'kudos-donations')}</option>
                    <option value="9">{__('9 years', 'kudos-donations')}</option>
                    <option value="10">{__('10 years', 'kudos-donations')}</option>
                </select>
            </label>
            {errors?.recurring_length &&
                <small className="error">{errors?.recurring_length?.message}</small>}
        </FormTab>
  )
}

export default PaymentFrequency
