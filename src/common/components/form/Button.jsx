import classNames from 'classnames'
import React from 'react'

function Button ({
  type = 'button', children, ariaLabel, className, onClick
}) {
  return (
        <button
            type={type}
            onClick={onClick}
            className={classNames(className, 'bg-primary hover:bg-primary-dark text-base text-white font-bold py-3 px-5 rounded-lg border-none inline-flex items-center transition ease-in-out focus:ring-primary focus:ring focus:ring-offset-2')}
            aria-label={ariaLabel}
        >
            {children}
        </button>

  )
}

export default Button
