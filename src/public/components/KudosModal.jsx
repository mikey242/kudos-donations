import React from 'react'
import { Dialog } from '@mui/material'
import { __ } from '@wordpress/i18n'
import { XIcon } from '@heroicons/react/solid'
import logo from '../../images/logo-colour.svg'
import { Transition } from '@headlessui/react'
import { forwardRef } from '@wordpress/element'

const KudosModal = forwardRef(({ toggle, isOpen, children, root }, ref) => {
  return (
        <Dialog
            open={isOpen}
            onClose={toggle}
            className="fixed z-1050 inset-0 overflow-y-auto"
            container={() => root.shadowRoot.getElementById('kudos')}
            BackdropComponent={() => <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>}
        >
            <Transition
                appear={true}
                show={isOpen}
                enter="transition-all duration-[400ms]"
                enterFrom="opacity-0 rotate-[-5deg] translate-x-3 translate-y-3 scale-90"
                enterTo="opacity-100 rotate-0 translate-x-0 translate-y-0 scale-100"
                leave="transform duration-200 transition ease-in-out"
                leaveFrom="opacity-100 rotate-0 scale-100 "
                leaveTo="opacity-0 scale-95 "
            >
                <div
                    className="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    {/* This element is to trick the browser into centering the modal contents. */}
                    <span className="hidden sm:inline-block sm:align-middle sm:h-screen"
                          aria-hidden="true">&#8203;</span>
                    <div
                        id="kudos-modal"
                        ref={ref}
                        className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all duration-200 sm:align-middle sm:max-w-lg sm:w-full"
                    >
                        <div className="bg-white p-8">
                            <div className="kudos-modal-header flex items-center justify-between">
                                <span className="mr-3 inline-block flex" title="Kudos Donations">
                                    <img alt="Kudos logo" className="h-6" src={logo}/>
                                </span>
                                <button
                                    className="bg-transparent transition p-0 inline leading-none border-0 focus:outline-none focus:ring hover:text-primary-dark ring-primary ring-offset-2 rounded-full w-5 h-5 cursor-pointer text-center"
                                    onClick={toggle}
                                    type="button"
                                    title={__('Close modal', 'kudos-donations')}
                                >
                                    <XIcon className="align-middle w-5 h-5"/>
                                </button>
                            </div>
                            <div className="mt-2">
                                {children}
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Dialog>
  )
})

export default KudosModal
