import React from 'react';
import { Transition } from '@headlessui/react';
import { Pane } from '../Panel';
import {
	CheckCircleIcon,
	ExclamationCircleIcon,
} from '@heroicons/react/24/outline';

const Notification = ({ notifications, onNotificationClick }) => {
	return (
		<div className="fixed flex flex-col items-center bottom-5 left-1/2 -translate-x-1/2 z-1050 cursor-pointer">
			{notifications.map((t, i) => (
				<div
					className="fixed bottom-0 transition whitespace-nowrap"
					style={{ transform: `translateY(-${i * 80 + 'px'})` }}
					key={t.id}
				>
					<Transition
						appear={true}
						show={true}
						enter="transform transition duration-[400ms]"
						enterFrom="opacity-0 translate-y-full"
						enterTo="opacity-1 translate-y-0"
					>
						<Pane>
							<button
								onClick={() => onNotificationClick(t.id)}
								className="flex justify-around items-center p-5"
							>
								{t.success ? (
									<CheckCircleIcon className="w-5 h-5 mr-2 text-green-600" />
								) : (
									<ExclamationCircleIcon className="w-5 h-5 mr-2 text-red-600" />
								)}
								<span className="first-letter:uppercase">
									{t.text}
								</span>
							</button>
						</Pane>
					</Transition>
				</div>
			))}
		</div>
	);
};

export default Notification;
