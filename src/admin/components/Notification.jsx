import React from 'react'
import Panel from './Panel'
import {Transition} from '@headlessui/react'
import {CheckCircleIcon, ExclamationCircleIcon,} from '@heroicons/react/outline'

const Notification = ({success = true, shown, message, onClick}) => {
    return (
        <div className="fixed bottom-5 left-1/2 -translate-x-1/2 z-1050 cursor-pointer" onClick={onClick}>
            <Transition
                show={shown}
                enter="transform transition duration-[400ms]"
                enterFrom="translate-y-full"
                enterTo="translate-y-0"
                leave="transform duration-200 transition ease-in-out"
                leaveFrom="opacity-100 scale-100 "
                leaveTo="opacity-0 scale-95 "
            >
                <Panel>
                    <div className="flex justify-around items-center p-5">
                        {success ? (
                            <CheckCircleIcon className="w-5 h-5 mr-2 text-green-600"/>
                        ) : (
                            <ExclamationCircleIcon className="w-5 h-5 mr-2 text-red-600"/>
                        )}
                        {message}
                    </div>
                </Panel>
            </Transition>
        </div>
    )
}

export default Notification
