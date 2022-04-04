import React from 'react'
import { Controller } from 'react-hook-form'
import { RadioGroup } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'
import classNames from 'classnames'

const RadioGroupControl = forwardRef(({ name, validation, options, label }, ref) => {
  return (
        <Controller
            name={name}
            validation={validation}
            ref={ref}
            render={({ field: { onChange, value } }) => (
                <RadioGroup value={value} onChange={onChange} className="mt-2">
                    <RadioGroup.Label
                        className={label ? 'block text-sm font-medium font-bold text-gray-700 mb-1' : 'sr-only'}>{label}</RadioGroup.Label>
                    <div className="grid gap-3 grid-flow-col auto-cols-fr">
                        {options.map((option, i) => (
                            <RadioGroup.Option
                                key={i}
                                value={option.value}
                                className={({ active, checked }) =>
                                  classNames(
                                    active ? 'ring-2 ring-offset-2 ring-primary' : '',
                                    checked
                                      ? 'bg-primary border-transparent text-white'
                                      : 'bg-white border-gray-300 text-gray-900 hover:bg-gray-50',
                                    'transition ease-in-out cursor-pointer focus:outline-none border rounded-md py-3 px-3 flex items-center justify-center text-sm font-medium sm:flex-1'
                                  )
                                }
                                disabled={option.disabled}
                            >
                                <RadioGroup.Label as="p">{option.label}</RadioGroup.Label>
                            </RadioGroup.Option>
                        ))}
                    </div>
                </RadioGroup>
            )}
        />
  )
})

export default RadioGroupControl
