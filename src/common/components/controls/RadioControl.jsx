import React from 'react'
import { useFormContext } from 'react-hook-form'

const RadioControl = ({ name, validation, options, label, help }) => {
  const {
    getValues,
    register
  } = useFormContext()

  return (
        <div className="mt-4">
            <label className="block text-sm font-medium font-bold text-gray-700">{label}</label>
            {help && <p className="text-sm leading-5 text-gray-500">{help}</p>}
            <fieldset className="mt-4">
                <legend className="sr-only">Notification method</legend>
                <div className="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                    {options.map((option, index) => (
                        <div key={option.id} className="flex items-center">
                            <input
                                {...register(name, validation)}
                                id={option.id}
                                value={option.id}
                                type="radio"
                                defaultChecked={!!((!getValues(name) && index === 0))}
                                className="focus:ring-primary h-4 w-4 text-primary border-gray-300"
                            />
                            <label htmlFor={option.id} className="ml-3 block text-sm font-medium text-gray-700">
                                {option.label}
                            </label>
                        </div>
                    ))}
                </div>
            </fieldset>
        </div>

  // <div className="flex justify-center items-center mt-4">
  //
  //     {options.map((option, index) => (
  //         <label key={index} className="flex items-center cursor-pointer font-normal mr-2 mt-2">
  //             <input
  //                 {...register(name, validation)}
  //                 className={`
  //                       before:content-none shadow-none appearance-none text-left placeholder-gray-500 ring-offset-2 border-2 border-gray-300 transition ease-in-out bg-white rounded-full inline-block w-4 h-4 m-2
  //                       focus:border-primary focus:outline-none focus:ring-primary focus:ring-2 ring-offset-2
  //                       checked:bg-primary checked:border-primary
  //                     `}
  //                 type="radio"
  //                 value={option.value}
  //                 aria-label={option.label}
  //                 defaultChecked={!!((!getValues(name) && index === 0))}
  //             />
  //             {option.label}
  //         </label>
  //     ))}
  // </div>

  )
}

export default RadioControl
