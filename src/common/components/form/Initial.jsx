import { __ } from '@wordpress/i18n'
import classNames from 'classnames'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'

function Initial (props) {
  const { values, title, description } = props

  const {
    register,
    formState: { errors }
  } = useFormContext()

  return (
        <FormTab title={title} description={description}>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('value', { required: true, min: 1, max: 5000 })}
                    type="number"
                    inputMode="numeric"
                    className={classNames('border-gray-300', 'appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full')}
                    placeholder={`${__('Amount', 'kudos-donations')} (€)`}
                    aria-label={__('Amount', 'kudos-donations')}
                />
            </label>
            {errors.value && <small className="error">{__('Minimum donation is 1 euro', 'kudos-donations')}</small>}
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('name', { required: true })}
                    name="name"
                    placeholder={__('Name', 'kudos-donations')}
                    className="appearance-none m-0 text-left placeholder-gray-500 border border-solid border-gray-300 transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full"
                    type="text"
                />
            </label>
            {errors.name && <small className="error">{__('Your name is required', 'kudos-donations')}</small>}
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('email', { required: true })}
                    placeholder={__('Email', 'kudos-donations')}
                    className="appearance-none m-0 text-left placeholder-gray-500 border border-solid border-gray-300 transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full"
                    type="email"
                />
            </label>
            {errors.name && <small className="error">{__('Your email is required', 'kudos-donations')}</small>}

            <div className="flex justify-center items-center mt-4">
                <label className="flex items-center cursor-pointer font-normal mr-2 mt-2">
                    <input
                        {...register('payment_frequency')}
                        name="payment_frequency"
                        className="appearance-none m-0 text-left placeholder-gray-500 border border-solid border-gray-300 transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none checked:bg-radio-checked checked:bg-primary transition ease-in-out rounded-full border-primary inline-block w-4 h-4 m-2 p-0 focus:ring-primary focus:ring ring-offset-2"
                        type="radio"
                        value="oneoff"
                        aria-label={__('One-off', 'kudos-donations')}
                        defaultChecked={values.payment_frequency === 'oneoff'}
                    />
                    {__('One-off', 'kudos-donations')}
                </label>
                <label className="flex items-center cursor-pointer font-normal mr-2 mt-2">
                    <input
                        {...register('payment_frequency')}
                        name="payment_frequency"
                        className="appearance-none m-0 text-left placeholder-gray-500 border border-solid border-gray-300 transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none checked:bg-radio-checked checked:bg-primary transition ease-in-out rounded-full border-primary inline-block w-4 h-4 m-2 p-0 focus:ring-primary focus:ring ring-offset-2"
                        type="radio"
                        value="recurring"
                        aria-label={__('Recurring', 'kudos-donations')}
                        defaultChecked={values.payment_frequency === 'recurring'}
                    />
                    {__('Recurring', 'kudos-donations')}
                </label>
            </div>
        </FormTab>
  )
}

export default Initial
