import React from 'react'
import { forwardRef } from '@wordpress/element'
import { SelectorIcon } from '@heroicons/react/solid'

const SelectControl = forwardRef(({ onChange, onBlur, name, label, options, placeholder }, ref) => (
    <div className="relative w-full">
        <label className="flex cursor-pointer font-normal mt-2 w-full">
            <SelectorIcon
                className={'text-gray-300 absolute w-5 right-0 mr-3 bottom-1/2 translate-y-1/2'}/>
            <select
                name={name}
                ref={ref}
                onChange={onChange}
                onBlur={onBlur}
                defaultValue=""
                className={'cursor-pointer relative z-0 pr-10 appearance-none bg-transparent border-gray-300 border border-solid focus:border-primary focus:ring-primary transition-colors ease-in-out rounded w-full py-2 px-3 text-gray-700'}
            >
                {placeholder && (
                    <option disabled key={`placeholder_${name}`} value="">{placeholder}</option>)}
                {options.map((entry, index) => (
                        <option key={index} value={entry.value}>{entry.label}</option>
                )
                )}
            </select>
        </label>
    </div>
))

export default SelectControl
