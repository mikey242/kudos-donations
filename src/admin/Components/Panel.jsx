import React from 'react'

const Panel = ({ children, className }) => {
  return (
        <div className={` ${className} relative mt-5 w-full overflow-x-auto bg-white shadow-md sm:rounded-lg`}>
            {children}
        </div>
  )
}

export default Panel
