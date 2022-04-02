import React from 'react'
import { useFormContext } from 'react-hook-form'
import BaseControl from './BaseControl'

const RadioControl = ({ name, validation, options }) => {
  const {
    getValues,
    register
  } = useFormContext()

  return (
        <div className="flex justify-center items-center mt-4">

            {options.map((option, index) => (
                <label key={index} className="flex items-center cursor-pointer font-normal mr-2 mt-2">
                    <input
                        {...register(name, validation)}
                        className={`
                          before:content-none shadow-none appearance-none text-left placeholder-gray-500 ring-offset-2 border-2 border-gray-300 transition ease-in-out bg-white rounded-full inline-block w-4 h-4 m-2 
                          focus:border-primary focus:outline-none focus:ring-primary focus:ring-2 ring-offset-2
                          checked:bg-primary checked:border-primary 
                        `}
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
