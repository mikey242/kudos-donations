import React from 'react'
import { Controller } from 'react-hook-form'
import { Switch } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'
import BaseControl from './BaseControl'
import classNames from 'classnames'

const ToggleControl = forwardRef(({ name, validation, label, help }, ref) => {
  return (
        <Controller
            name={name}
            validation={validation}
            ref={ref}
            render={({ field: { value, onChange } }) => (
                <Switch.Group as="div" className="flex items-center mt-3 first:mt-0">
                    <Switch
                        checked={value}
                        onChange={onChange}
                        className={classNames(
                          value ? 'bg-primary' : 'bg-gray-200',
                          'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary'
                        )}
                    >
                        <span
                            aria-hidden="true"
                            className={classNames(
                              value ? 'translate-x-5' : 'translate-x-0',
                              'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200'
                            )}
                        />
                    </Switch>
                    <Switch.Label as="span" className="ml-3 cursor-pointer">
                        <span className="text-sm font-medium text-gray-900">{label}</span>
                    </Switch.Label>
                </Switch.Group>
            )}
        />
  )
})

export default ToggleControl
