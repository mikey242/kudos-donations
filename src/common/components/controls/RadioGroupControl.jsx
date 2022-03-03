import React from 'react'
import { useFormContext, Controller } from 'react-hook-form'
import { RadioGroup } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'

const RadioGroupControl = forwardRef(({ name, validation, options, label }, ref) => {
  const {
    getValues
  } = useFormContext()

  return (
        <Controller
            name={name}
            validation={validation}
            ref={ref}
            render={({ field: { onChange } }) => (
                <RadioGroup value={getValues(name)} onChange={onChange}>
                    <RadioGroup.Label>Plan</RadioGroup.Label>
                    <div className="flex flex-row flex-wrap">
                        {options.map((option, i) => (
                            <RadioGroup.Option key={i} value={option.value} className="flex-grow">
                                {({ checked }) => (
                                    <span
                                        className={checked ? 'bg-primary text-white' : ''}
                                    >{option.label}</span>
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
