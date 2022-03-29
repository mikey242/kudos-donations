import React from 'react'
import { useFormContext } from 'react-hook-form'

const TextAreaControl = ({ name, validation, placeholder, label }) => {
  const { register, formState: { errors } } = useFormContext()

  return (
        <>
            <label className="flex flex-wrap items-start cursor-pointer font-normal mt-2 w-full">
                {label &&
                    <span className="mb-1">
                            {label}
                        </span>
                }
                <textarea
                    {...register(name, validation)}
                    className={'border-gray-300 appearance-none m-0 text-left placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 order-1 py-2 px-3 rounded w-full'}
                    placeholder={placeholder}
                    aria-label={placeholder}
                />
            </label>
            {errors[name] && <div><small className="error">{errors[name].message}</small></div>}
        </>
  )
}

export default TextAreaControl
