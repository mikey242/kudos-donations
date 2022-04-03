import React from 'react'

const BaseControl = ({ label, help, error, children, isInline, className }) => {
  return (
        <label
            className={`${className ?? ''} ${isInline ? 'inline-block' : 'block'} cursor-pointer mt-2`}>
            {label &&
                <p className={`${isInline && 'inline align-middle'} mt-2 mb-2 mr-4`}>
                    {label}
                </p>
            }
            {children}
            {error &&
                <div className={'mt-1'}><small className="text-red-500">{error}</small>
                </div>}
            {help &&
                <p className="my-2 text-xs text-gray-500">
                    {help}
                </p>
            }
        </label>
  )
}

export default BaseControl
