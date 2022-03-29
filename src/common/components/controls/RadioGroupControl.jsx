import React from 'react'
import { Controller } from 'react-hook-form'
import { RadioGroup } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'

const RadioGroupControl = forwardRef(({ name, validation, options, label }, ref) => {
  return (
        <Controller
            name={name}
            validation={validation}
            ref={ref}
            render={({ field: { onChange, value } }) => (
                <RadioGroup value={value} onChange={onChange}>
                    {label &&
                        <p className="mb-1">
                            {label}
                        </p>
                    }
                    <div className="grid grid-flow-row grid-cols-[repeat(auto-fit,_minmax(75px,_auto))] gap-3">
                        {options.map((option, i) => (
                            <RadioGroup.Option key={i} value={option.value} disabled={option.disabled}
                                               className="flex-grow">
                                {({ checked }) => (
                                    <span
                                        className={`${checked ? 'bg-primary border-primary ring-2 text-white' : 'bg-white border-gray-300'}
                                        ${option.disabled && 'opacity-50'}
                                        px-5 py-3 ring-offset-2 ring-primary cursor-pointer flex justify-center rounded border border-solid transition ease-in-out duration-75 focus:border-primary focus:outline-none focus:ring-0`}
                                    >
                                   {option.label}</span>
                                )}
                            </RadioGroup.Option>
                        ))}
                    </div>
                </RadioGroup>
            )}
        />
  )
})

export default RadioGroupControl
