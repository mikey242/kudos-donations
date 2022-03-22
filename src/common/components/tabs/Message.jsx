import { __ } from '@wordpress/i18n'
import React from 'react'
import { useFormContext } from 'react-hook-form'
import FormTab from './FormTab'
import { useEffect } from '@wordpress/element'

const Message = (props) => {
  const { title, description, buttons } = props

  const {
    register,
    setFocus
  } = useFormContext()

  useEffect(() => {
    setFocus('message')
  }, [setFocus])

  return (
        <FormTab title={title} description={description} buttons={buttons}>
            <label className="flex cursor-pointer font-normal mt-2 w-full">
                <textarea
                    {...register('message', {})}
                    className={'border-gray-300 appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full'}
                    placeholder={`${__('Message', 'kudos-donations')}`}
                    aria-label={__('Message', 'kudos-donations')}
                />
            </label>
        </FormTab>
  )
}

export default Message
