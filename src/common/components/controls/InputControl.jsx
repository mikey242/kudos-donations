import React from 'react'
import { useFormContext } from 'react-hook-form'
import BaseControl from './BaseControl'

const InputControl = ({ name, validation, label, help, type = 'text', placeholder }) => {
  const { register, formState: { errors } } = useFormContext()

  return (
        <BaseControl label={label} help={help} error={errors[name]?.message}>
            <div className="relative w-full">
                <input
                    {...register(name, validation)}
                    type={type}
                    className={'border-gray-300 m-0 placeholder-gray-500 border border-solid transition ease-in-out duration-75 leading-6 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 py-2 px-3 rounded w-full'}
                    placeholder={placeholder}
                    aria-label={placeholder}
                />
            </div>
        </BaseControl>
  )
}

export default InputControl
