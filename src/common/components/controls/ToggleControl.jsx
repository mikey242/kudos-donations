import React from 'react'
import { useFormContext, Controller } from 'react-hook-form'
import { Switch } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'

const ToggleControl = forwardRef(({ name, validation, label }, ref) => {
  const {
    getValues
  } = useFormContext()

  return (
        <Controller
            name={name}
            validation={validation}
            ref={ref}
            render={({ field: { onChange } }) => (
                <div className="flex items-center justify-center font-normal mt-2 w-full">
                    <Switch.Group>
                        <Switch.Label className="mr-4 cursor-pointer">{label}</Switch.Label>
                        <Switch
                            ad="div"
                            checked={getValues(name)}
                            onChange={(e) => onChange(e)}
                            className={`${
                                getValues(name) ? 'bg-primary' : 'bg-gray-300'
                            } relative inline-flex items-center h-6 rounded-full w-11 transition-colors cursor-pointer border-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary`}
                        >
                              <span
                                  className={`${
                                      getValues(name) ? 'translate-x-6' : 'translate-x-1'
                                  } inline-block w-4 h-4 transform bg-white rounded-full transition-transform`}
                              />
                        </Switch>
                    </Switch.Group>
                </div>
            )}
        />

  )
})

export default ToggleControl