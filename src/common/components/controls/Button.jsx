import classNames from 'classnames'
import React from 'react'

function Button ({
  type = 'button', children, href, isOutline, isDisabled, ariaLabel, className, onClick, form
}) {
  const handleClick = (e) => {
    if (href) {
      e.preventDefault()
      location.href = href
    } else {
      typeof onClick === 'function' && onClick()
    }
  }

  return (
        <button
            type={type}
            onClick={handleClick}
            form={form}
            className={classNames(
              className,
              isDisabled && 'cursor-not-allowed',
              isOutline ? 'border-primary border text-primary' : 'border-none',
              'relative text-white font-bold px-5 focus:ring z-1 group cursor-pointer overflow-hidden py-3 rounded-lg flex items-center transition ease-in-out focus:ring-primary focus:ring-offset-2'
            )}
            aria-label={ariaLabel}
        >
            {children}
            <div
                className={classNames(
                  isOutline ? 'bg-none' : 'bg-primary',
                  'absolute -z-1 w-full h-full top-0 left-0 group-hover:brightness-90 transition ease-in-out'
                )}/>
        </button>

  )
}

export { Button }
