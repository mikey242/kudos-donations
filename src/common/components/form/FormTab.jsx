import React from 'react'
import { Transition } from '@headlessui/react'

function FormTab (props) {
  const {
    title, description, children
  } = props

  return (
        <Transition
            appear={true}
            enter="transition-all duration-[400ms]"
            enterFrom="opacity-0 translate-x-3"
            enterTo="opacity-100 translate-x-0"
        >
            <div
                className="form-tab block w-full relative mt-4 p-0 border-0"
            >
                <legend className="block m-auto">
                    <h2 className="kudos_modal_title font-normal font-serif text-4xl m-0 mb-2 text-gray-900 block text-center">
                        {title}
                    </h2>
                </legend>
                <p className="text-lg text-gray-900 text-center block font-normal mb-4">
                    {description}
                </p>
                {children}
            </div>
        </Transition>
  )
}

export default FormTab
