import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { useEffect, useMemo } from '@wordpress/element'
import countryList from 'react-select-country-list'
import SelectControl from './SelectControl'
import InputControl from './InputControl'

function Address (props) {
  const { title, description, campaign } = props
  const countryOptions = useMemo(() => countryList().getData(), [])
  const {
    register,
    setFocus,
    formState: { errors }
  } = useFormContext()

  useEffect(() => {
    setFocus('business_name')
  }, [setFocus])

  return (
        <FormTab title={title} description={description}>
            <InputControl {...register('business_name')}
                          placeholder={__('Business name', 'kudos-donations')}/>
            <InputControl {...register('street', { required: campaign.address_required })}
                          placeholder={__('Street', 'kudos-donations')}/>
            {errors.street && <small className="error">{__('Street required', 'kudos-donations')}</small>}
            <InputControl {...register('postcode', { required: campaign.address_required })}
                          placeholder={__('Postcode', 'kudos-donations')}/>
            {errors.postcode && <small className="error">{__('Postcode required', 'kudos-donations')}</small>}
            <InputControl {...register('city', { required: campaign.address_required })}
                          placeholder={__('City', 'kudos-donations')}/>
            {errors.city && <small className="error">{__('City required', 'kudos-donations')}</small>}
            <SelectControl {...register('country', { required: campaign.address_required })}
                           placeholder={__('Country', 'kudos-donations')}
                           options={countryOptions}/>
            {errors.country && <small className="error">{__('Country required', 'kudos-donations')}</small>}
        </FormTab>
  )
}

export default Address
