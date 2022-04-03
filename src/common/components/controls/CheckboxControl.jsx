import React from 'react'
import { useFormContext } from 'react-hook-form'
import BaseControl from './BaseControl'

const CheckboxControl = ({ name, validation, label, help, placeholder }) => {
  const { register, formState: { errors } } = useFormContext()

  return (
        <BaseControl isInline label={label} help={help} error={errors[name]?.message}>
            <div className="relative float-left">
                <input
                    {...register(name, validation)}
                    type="checkbox"
                    className={'accent-primary inline align-middle h-4 w-4 border border-gray-300 rounded-sm bg-white checked:border-primary ring-primary focus:ring ring-offset-2 transition mr-2 cursor-pointer'}
                    placeholder={placeholder}
                    aria-label={placeholder}
                />
            </div>
        </BaseControl>
  )
}

export default CheckboxControl
