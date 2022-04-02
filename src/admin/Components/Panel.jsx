import React from 'react'
import { forwardRef } from '@wordpress/element'

const Panel = forwardRef(({ children, title, className = '' }, ref) => {
  return (
        <div>
            {title && <h2 className="text-center my-5">{title}</h2>}
            <div ref={ref}
                 className={`${className} mt-5 w-full bg-white shadow-md sm:rounded-lg z-1`}>
                {children}
            </div>
        </div>
  )
})

export default Panel
