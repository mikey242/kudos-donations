import React from 'react'
import { useFormContext } from 'react-hook-form'

const InputControl = ({ name, error, validation, type = 'text', placeholder }) => {
  const { register, formState: { errors } } = useFormContext()

  return (

        <>
            <div className="relative w-full">
                <label className="flex cursor-pointer mt-2">
                    <input
                        {...register(name, validation)}
                        type={type}
                        className={'border-gray-300 m-0 placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 py-2 px-3 rounded w-full'}
                        placeholder={placeholder}
                        aria-label={placeholder}
                    />
                </label>
            </div>
            {errors[name] && <small className="error">{error}</small>}
        </>
  )
}

export default InputControl
