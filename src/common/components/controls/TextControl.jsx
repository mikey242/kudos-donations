import React from 'react'
import {useFormContext} from 'react-hook-form'
import {ExclamationCircleIcon} from '@heroicons/react/outline'
import classNames from 'classnames'
import {get, uniqueId} from 'lodash'

const TextControl = ({
                         name,
                         validation,
                         label,
                         help,
                         addOn,
                         type = 'text',
                         placeholder,
                     }) => {
    const {
        register,
        formState: {errors},
    } = useFormContext()

    const error = get(errors, name)
    const id = uniqueId(name + '-')

    return (
        <div className="first:mt-0 mt-3">
            <label
                htmlFor={id}
                className="block text-sm font-bold text-gray-700"
            >
                {label}
            </label>
            {help && <p className="text-sm leading-5 text-gray-500">{help}</p>}
            <div className="mt-1 relative rounded-md shadow-sm">
                {addOn && (
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
						<span className="text-gray-500 sm:text-sm">
							{addOn}
						</span>
                    </div>
                )}
                <input
                    {...register(name, validation)}
                    type={type}
                    id={id}
                    className={classNames(
                        error?.message
                            ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 '
                            : 'border-gray-300 focus:ring-primary focus:border-primary',
                        addOn && 'pl-7',
                        'form-input transition ease-in-out shadow-none block w-full pr-10 focus:outline-none sm:text-sm rounded-md'
                    )}
                    placeholder={placeholder}
                    aria-invalid={!!error}
                    aria-errormessage={`${id}-error`}
                />
                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    {error?.message && type !== 'hidden' && (
                        <ExclamationCircleIcon
                            className="h-5 w-5 text-red-500"
                            aria-hidden="true"
                        />
                    )}
                </div>
            </div>
            {error?.message && (
                <p className="mt-2 text-sm text-red-600" id={`${id}-error`}>
                    {error?.message}
                </p>
            )}
        </div>
    )
}

export {TextControl}
