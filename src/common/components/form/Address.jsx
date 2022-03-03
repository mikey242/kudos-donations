import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { useEffect, useMemo } from '@wordpress/element'
import countryList from 'react-select-country-list'
import SelectControl from '../controls/SelectControl'
import InputControl from '../controls/InputControl'

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
            <InputControl name="business_name"
                          placeholder={__('Business name', 'kudos-donations')}/>
            <InputControl name="street" error={__('Street required', 'kudos-donations')}
                          validation={{ required: campaign.address_required }}
                          placeholder={__('Street', 'kudos-donations')}/>
            <InputControl name="postcode" error={__('Postcode required', 'kudos-donations')}
                          validation={{ required: campaign.address_required }}
                          placeholder={__('Postcode', 'kudos-donations')}/>
            {/* <InputControl {...register('city', { required: campaign.address_required })} */}
            {/*              placeholder={__('City', 'kudos-donations')}/> */}
            {/* {errors.city && <small className="error">{__('City required', 'kudos-donations')}</small>} */}
            {/* <SelectControl {...register('country', { required: campaign.address_required })} */}
            {/*               placeholder={__('Country', 'kudos-donations')} */}
            {/*               options={countryOptions}/> */}
            {/* {errors.country && <small className="error">{__('Country required', 'kudos-donations')}</small>} */}
        </FormTab>
  )
}

export default Address
