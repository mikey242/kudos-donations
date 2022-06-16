import classNames from 'classnames'
import React from 'react'
import {forwardRef} from '@wordpress/element'

const Button = forwardRef(
    (
        {
            type = 'button',
            children,
            href,
            isOutline,
            isDisabled,
            color,
            ariaLabel,
            className,
            onClick,
            form,
        },
        ref
    ) => {
        const handleClick = (e) => {
            if (href) {
                e.preventDefault()
                window.location.href = href
            } else {
                return typeof onClick === 'function' && onClick()
            }
        }

        return (
            <button
                type={type}
                onClick={handleClick}
                style={{backgroundColor: color}}
                form={form}
                ref={ref}
                disabled={isDisabled}
                className={classNames(
                    className,
                    isDisabled && 'cursor-not-allowed',
                    isOutline
                        ? 'border-primary border text-primary'
                        : 'border-none text-white',
                    'relative font-bold px-3 py-2 xl:px-5 xl:py-3 focus:ring z-1 group cursor-pointer overflow-hidden rounded-lg flex items-center transition ease-in-out focus:ring-primary focus:ring-offset-2'
                )}
                aria-label={ariaLabel}
            >
                {children}
                <div
                    className={classNames(
                        isOutline ? 'bg-none' : 'bg-primary',
                        'absolute -z-1 w-full h-full top-0 left-0 group-hover:brightness-90 transition ease-in-out'
                    )}
                />
            </button>
        )
    }
)

export {Button}
