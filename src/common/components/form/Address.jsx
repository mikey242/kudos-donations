import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { useMemo } from '@wordpress/element'
import classNames from 'classnames'
import countryList from 'react-select-country-list'

function Address (props) {
  const { title, description, campaign } = props
  const countryOptions = useMemo(() => countryList().getData(), [])

  const {
    register,
    formState: { errors }
  } = useFormContext()

  return (
        <FormTab title={title} description={description}>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('business_name', {})}
                    type="text"
                    className={classNames('border-gray-300', 'appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full')}
                    placeholder={__('Business name', 'kudos-donations')}
                    aria-label={__('Business name', 'kudos-donations')}
                />
            </label>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('street', { required: campaign.address_required })}
                    type="text"
                    className={classNames('border-gray-300', 'appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full')}
                    placeholder={__('Street', 'kudos-donations')} aria-label={__('Street', 'kudos-donations')}
                />
            </label>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('postcode', { required: campaign.address_required })}
                    type="text"
                    className={classNames('border-gray-300', 'appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full')}
                    placeholder={__('Postcode', 'kudos-donations')} aria-label={__('Postcode', 'kudos-donations')}
                />
            </label>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <input
                    {...register('city', { required: campaign.address_required })}
                    type="text"
                    className={classNames('border-gray-300', 'appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full')}
                    placeholder={__('City', 'kudos-donations')} aria-label={__('City', 'kudos-donations')}
                />
            </label>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <select
                    {...register('country', { required: campaign.address_required })}
                    defaultValue=""
                    className="bg-select appearance-none bg-white bg-no-repeat bg-right-2 border-gray-300 border border-solid focus:border-primary focus:ring-primary transition-colors ease-in-out rounded w-full py-2 px-3 text-gray-700"
                >
                    <option value="" disabled>{__('Country', 'kudos-donations')}</option>
                    {countryOptions.map((country) => (
                        <option key={country.value} value={country.label}>{country.label}</option>
                    ))}
                </select>
            </label>
        </FormTab>
  )
}

export default Address
