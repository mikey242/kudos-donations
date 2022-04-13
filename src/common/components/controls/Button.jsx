import classNames from 'classnames'
import React from 'react'

function Button ({
  type = 'button', children, isLink, isDisabled, ariaLabel, className, onClick, form
}) {
  return (
        <button
            type={type}
            onClick={onClick}
            form={form}
            className={classNames(
              className,
              isDisabled && 'cursor-not-allowed',
              isLink ? 'underline' : 'text-white font-bold px-5 focus:ring',
              'relative z-1 group cursor-pointer overflow-hidden py-3 rounded-lg border-none flex items-center transition ease-in-out focus:ring-primary focus:ring-offset-2'
            )}
            aria-label={ariaLabel}
        >
            {children}
            <div
                className={classNames(
                  !isLink && 'bg-primary',
                  'absolute -z-1 w-full h-full top-0 left-0 group-hover:brightness-90 transition ease-in-out'
                )}/>
        </button>

  )
}

export { Button }
