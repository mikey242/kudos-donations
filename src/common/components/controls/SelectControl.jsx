import React from 'react'
import { useFormContext } from 'react-hook-form'

const SelectControl = ({ name, label, validation, options, placeholder }) => {
  const { register, formState: { errors } } = useFormContext()

  return (
        <div className="first:mt-0 mt-3">
            <label htmlFor={name} className={label ? 'block text-sm font-medium text-gray-700' : 'sr-only'}>
                {label}
            </label>
            <select
                {...register(name, validation)}
                defaultValue=""
                className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
            >
                {placeholder && (<option disabled key={`placeholder_${name}`} value="">{placeholder}</option>)}
                {options.map((entry, index) =>
                    <option key={index} value={entry.value}>{entry.label}</option>
                )}
            </select>
            {errors[name]?.message &&
                <p className="mt-2 text-sm text-red-600" id="email-error">
                    {errors[name]?.message}
                </p>
            }
        </div>

  // <div className="relative w-full">
  //     <label className="flex cursor-pointer font-normal mt-2 w-full">
  //         <SelectorIcon
  //             className={'text-gray-300 absolute w-5 right-0 mr-3 bottom-1/2 translate-y-1/2'}/>
  //         <select
  //             {...register(name, validation)}
  //             defaultValue=""
  //             className={'cursor-pointer relative z-0 pr-10 appearance-none bg-transparent border-gray-300 border border-solid focus:border-primary focus:ring-primary transition-colors ease-in-out rounded w-full py-2 px-3 text-gray-700'}
  //         >
  //             {placeholder && (<option disabled key={`placeholder_${name}`} value="">{placeholder}</option>)}
  //             {options.map((entry, index) => (
  //                     <option key={index} value={entry.value}>{entry.label}</option>
  //                 )
  //             )}
  //         </select>
  //     </label>
  // </div>

  )
}

export default SelectControl
