import React from 'react'
import Panel from './Panel'
import { Transition } from '@headlessui/react'
import { CheckCircleIcon } from '@heroicons/react/outline'

const Notification = ({ notification }) => {
  return (
        <div className="absolute bottom-0 right-1/2 translate-x-1/2 z-1050">
            <Transition
                show={notification?.shown}
                enter="transform transition duration-[400ms]"
                enterFrom="opacity-0 rotate-[-120deg] scale-50"
                enterTo="opacity-100 rotate-0 scale-100"
                leave="transform duration-200 transition ease-in-out"
                leaveFrom="opacity-100 rotate-0 scale-100 "
                leaveTo="opacity-0 scale-95 "
            >
                <Panel>
                    <div className="flex justify-around items-center p-5">
                        <CheckCircleIcon className="w-5 h-5 mr-2 text-green-600"/>{notification.message}
                    </div>
                </Panel>

            </Transition>
        </div>
  )
}

export default Notification
