import React from 'react'
import { Controller } from 'react-hook-form'
import { RadioGroup } from '@headlessui/react'
import classNames from 'classnames'

const RadioGroupControl = ({ name, validation, options, help, label }) => {
  return (
        <Controller
            name={name}
            validation={validation}
            render={({ field: { onChange, value } }) => (
                <RadioGroup value={value} onChange={onChange} className="mt-2">
                    <RadioGroup.Label
                        className={label ? 'block text-sm font-medium font-bold text-gray-700 mb-1' : 'sr-only'}>{label}</RadioGroup.Label>
                    {help && <p className="text-sm leading-5 text-gray-500">{help}</p>}
                    <div className="grid gap-3 mt-1 grid-flow-col auto-cols-fr">
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
}

export { RadioGroupControl }
