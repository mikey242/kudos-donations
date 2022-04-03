import React from 'react'
import { useFormContext } from 'react-hook-form'

const TextAreaControl = ({ name, validation, placeholder, label, help }) => {
  const { register, formState: { errors } } = useFormContext()

  return (
        <div className="first:mt-0 mt-3">
            <label htmlFor={name} className="block text-sm font-medium text-gray-700">
                {label}
            </label>
            <div className="mt-1">
        <textarea
            rows={4}
            {...register(name, validation)}
            placeholder={placeholder}
            className="shadow-sm focus:ring-indigo-500 focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
            defaultValue={''}
        />
            </div>
            {errors[name]?.message &&
                <p className="mt-2 text-sm text-red-600" id="email-error">
                    {errors[name]?.message}
                </p>
            }
        </div>
  )
}

export default TextAreaControl
