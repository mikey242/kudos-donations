import classNames from 'classnames'
import React from 'react'

function Button ({
  type = 'button', children, ariaLabel, className, onClick
}) {
  return (
        <button
            type={type}
            onClick={onClick}
            className={classNames(className, 'border-none bg-primary hover:bg-primary-dark w-auto h-auto inline-flex items-center select-none py-3 px-5 rounded-lg cursor-pointer shadow-none transition ease-in-out focus:ring-primary focus:ring focus:ring-offset-2 text-center text-white leading-normal font-normal normal-case no-underline')}
            aria-label={ariaLabel}
        >
            {children}
        </button>

  )
}

export default Button
