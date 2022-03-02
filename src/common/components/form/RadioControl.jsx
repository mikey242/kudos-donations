import React from 'react'
import { __ } from '@wordpress/i18n'
import { useFormContext } from 'react-hook-form'

const RadioControl = ({ name, validation, options }) => {
  const {
    getValues,
    register,
    formState: { errors }
  } = useFormContext()

  return (
        <div className="flex justify-center items-center mt-4">

            {options.map((option, index) => (
                <label key={index} className="flex items-center cursor-pointer font-normal mr-2 mt-2">
                    <input
                        {...register(name, validation)}
                        key={`${name}.${index}.${option.label}`}
                        className="appearance-none m-0 text-left placeholder-gray-500 border border-solid border-gray-300 transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none checked:bg-radio-checked checked:bg-primary transition ease-in-out rounded-full border-primary inline-block w-4 h-4 m-2 p-0 focus:ring-primary focus:ring ring-offset-2"
                        type="radio"
                        value={option.value}
                        aria-label={option.label}
                        defaultChecked={!!((!getValues(name) && index === 0))}
                    />
                    {option.label}
                </label>
            ))}
        </div>

  )
}

export default RadioControl
