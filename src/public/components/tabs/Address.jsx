import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { useEffect, useMemo } from '@wordpress/element'
import countryList from 'react-select-country-list'
import SelectControl from '../../../common/components/controls/SelectControl'
import TextControl from '../../../common/components/controls/TextControl'

function Address (props) {
  const { title, description, buttons, required } = props
  const countryOptions = useMemo(() => countryList().getData(), [])
  const {
    setFocus
  } = useFormContext()

  useEffect(() => {
    setFocus('business_name')
  }, [setFocus])

  return (
        <FormTab title={title} description={description} buttons={buttons}>
            <TextControl name="business_name"
                         placeholder={__('Business name', 'kudos-donations')}/>
            <TextControl name="street"
                         validation={{
                           required: {
                             value: required,
                             message: __('Street required', 'kudos-donations')
                           }
                         }}
                         placeholder={__('Street', 'kudos-donations')}/>
            <TextControl name="postcode"
                         validation={{
                           required: {
                             value: required,
                             message: __('Postcode required', 'kudos-donations')
                           }
                         }}
                         placeholder={__('Postcode', 'kudos-donations')}/>
            <TextControl name="city"
                         validation={{
                           required: {
                             value: required,
                             message: __('City required', 'kudos-donations')
                           }
                         }}
                         placeholder={__('City', 'kudos-donations')}/>
            <SelectControl name="country" placeholder={__('Country', 'kudos-donations')}
                           options={countryOptions}
                           validation={{
                             required: {
                               value: required,
                               message: __('Country required', 'kudos-donations')
                             }
                           }}
                           error={__('Country required', 'kudos-donations')}/>
        </FormTab>
  )
}

export default Address
