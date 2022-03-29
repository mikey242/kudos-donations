import React from 'react'
import { forwardRef } from '@wordpress/element'

const Panel = forwardRef(({ children, className = '' }, ref) => {
  return (
        <div ref={ref} className={`${className} relative mt-5 w-full overflow-x-auto bg-white shadow-md sm:rounded-lg`}>
            {children}
        </div>
  )
})

export default Panel
