import React from 'react'
import { forwardRef } from '@wordpress/element'

const InputControl = forwardRef(({ onChange, onBlur, name, label, type = 'text', placeholder }, ref) => (
    <div className="relative w-full">
        <label className="flex cursor-pointer mt-2">
            <input
                name={name}
                ref={ref}
                onChange={onChange}
                onBlur={onBlur}
                type={type}
                className={'border-gray-300 m-0 placeholder-gray-500 border border-solid transition ease-in-out duration-75 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 py-2 px-3 rounded w-full'}
                placeholder={placeholder}
                aria-label={placeholder}
            />
        </label>
    </div>
))

export default InputControl
