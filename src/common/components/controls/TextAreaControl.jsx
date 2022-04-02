import React from 'react'
import { useFormContext } from 'react-hook-form'
import BaseControl from './BaseControl'

const TextAreaControl = ({ name, validation, placeholder, label, help }) => {
  const { register, formState: { errors } } = useFormContext()

  return (
        <BaseControl label={label} help={help} error={errors[name]?.message}>
                <textarea
                    {...register(name, validation)}
                    className={'border-gray-300 appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full'}
                    placeholder={placeholder}
                    aria-label={placeholder}
                />
        </BaseControl>
  )
}

export default TextAreaControl
