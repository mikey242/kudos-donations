import React from 'react'
import { Controller } from 'react-hook-form'
import { Switch } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'

const ToggleControl = forwardRef(({ name, validation, label }, ref) => {
  return (
        <Controller
            name={name}
            validation={validation}
            ref={ref}
            render={({ field: { value, onChange } }) => (
                <div className="flex items-center font-normal my-3">
                    <Switch.Group>
                        <Switch.Label className="mr-4 cursor-pointer">{label}</Switch.Label>
                        <Switch
                            ad="div"
                            checked={value}
                            onChange={(e) => onChange(e)}
                            className={`${
                                value ? 'bg-primary' : 'bg-gray-300'
                            } relative inline-flex items-center h-6 rounded-full w-11 transition-colors cursor-pointer border-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary`}
                        >
                              <span
                                  className={`${
                                      value ? 'translate-x-6' : 'translate-x-1'
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
