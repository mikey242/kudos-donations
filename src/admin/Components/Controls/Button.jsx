import classNames from 'classnames'
import { Icon, update } from '@wordpress/icons'
import React from 'react'

function Button ({
  type = 'button', children, ariaLabel, onClick, isDisabled, isBusy
}) {
  return (
        <button
            type={type}
            onClick={(!isBusy || !isDisabled)
              ? onClick
              : () => {
                }}
            className={classNames(
              isDisabled && 'bg-orange-700 text-amber-400',
              'bg-orange-500 hover:bg-orange-700 cursor-pointer text-base text-white font-bold py-2 px-4 rounded-lg border-none inline-flex items-center transition ease-in-out focus:ring-primary focus:ring focus:ring-offset-2')}
            aria-label={ariaLabel}
        >
            {isBusy && <Icon fill={'white'} className={'animate-spin'} icon={update}/>}
            {children}
        </button>

  )
}

export default Button
