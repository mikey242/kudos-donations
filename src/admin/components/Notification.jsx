import React from 'react'
import Panel from './Panel'
import { Transition } from '@headlessui/react'
import { CheckCircleIcon } from '@heroicons/react/outline'

const Notification = ({ notification, onClick }) => {
  return (
        <div className="fixed bottom-5 right-5 z-1050 cursor-pointer">
            <Transition
                show={notification?.shown}
                enter="transform transition duration-[400ms]"
                enterFrom="translate-x-full"
                enterTo="translate-x-0"
                leave="transform duration-200 transition ease-in-out"
                leaveFrom="opacity-100 scale-100 "
                leaveTo="opacity-0 scale-95 "
                onClick={onClick}
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
