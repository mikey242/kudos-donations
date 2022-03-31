import classNames from 'classnames'
import React from 'react'

function Button ({
  type = 'button', children, ariaLabel, className, onClick
}) {
  return (
        <button
            type={type}
            onClick={onClick}
            className={classNames(className, 'relative z-1 group cursor-pointer text-white overflow-hidden font-bold py-3 px-5 rounded-lg border-none inline-flex items-center transition ease-in-out focus:ring-primary focus:ring focus:ring-offset-2')}
            aria-label={ariaLabel}
        >
            {children}
            <div
                className="absolute -z-1 w-full h-full top-0 left-0 bg-primary group-hover:brightness-90 transition ease-in-out"/>
        </button>

  )
}

export default Button
