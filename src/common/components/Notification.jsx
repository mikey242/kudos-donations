import React from 'react';
import { Transition } from '@headlessui/react';
import Panel from './Panel';
import {
	CheckCircleIcon,
	ExclamationCircleIcon,
} from '@heroicons/react/outline';
import {
	REMOVE,
	useNotificationContext,
} from '../../admin/contexts/NotificationContext';

export default function Notification({ notifications }) {
	const { notificationDispatch } = useNotificationContext();

	return (
		<div className="fixed bottom-5 left-1/2 -translate-x-1/2 z-1050 cursor-pointer">
			{notifications.map((t) => (
				<Transition
					appear={true}
					show={true}
					key={t.id}
					enter="transform transition duration-[400ms]"
					enterFrom="translate-y-full"
					enterTo="translate-y-0"
					leave="transform duration-200 transition ease-in-out"
					leaveFrom="opacity-100 scale-100 "
					leaveTo="opacity-0 scale-95 "
				>
					<Panel>
						<button
							onClick={() =>
								notificationDispatch({
									type: REMOVE,
									payload: { id: t.id },
								})
							}
							className="flex justify-around items-center p-5"
						>
							{t.success ? (
								<CheckCircleIcon className="w-5 h-5 mr-2 text-green-600" />
							) : (
								<ExclamationCircleIcon className="w-5 h-5 mr-2 text-red-600" />
							)}
							{t.content}
						</button>
					</Panel>
				</Transition>
			))}
		</div>
	);
}
