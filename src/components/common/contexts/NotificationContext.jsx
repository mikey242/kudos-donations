import React from 'react';
import { createContext, useContext, useState } from '@wordpress/element';
import Notification from '../../admin/Notification';

export const NotificationContext = createContext(null);

export const NotificationProvider = ({ children }) => {
	const [list, setList] = useState([]);
	const timer = 6000;

	const createNotification = (text, success = true) => {
		const id = +new Date();
		const timerId = setTimeout(() => hideNotification(id), timer); // Initial timer
		setList((prev) => [
			...prev,
			{
				id,
				success,
				text,
				timerId,
				show: true,
				isHovered: false,
			},
		]);
	};

	const hideNotification = (id) => {
		setList((prev) =>
			prev.map((notification) =>
				notification.id === id
					? { ...notification, show: false }
					: notification
			)
		);

		// Remove the notification from the list after it has fully animated out
		setTimeout(() => {
			deleteNotification(id);
		}, 300); // Delay should match the transition duration
	};

	const deleteNotification = (id) => {
		setList((prev) => prev.filter((el) => el.id !== id));
	};

	const clearNotifications = () => {
		list.forEach(({ timerId }) => clearTimeout(timerId));
		setList([]);
	};

	const handleMouseEnter = (id) => {
		setList((prev) =>
			prev.map((notification) => {
				if (notification.id === id) {
					// Clear the existing timeout
					if (notification.timerId) {
						clearTimeout(notification.timerId);
					}
					return { ...notification, timerId: null }; // Remove timerId since it's cleared
				}
				return notification;
			})
		);
	};

	const handleMouseLeave = (id) => {
		setList((prev) =>
			prev.map((notification) => {
				if (notification.id === id) {
					// Restart the timeout
					const newTimerId = setTimeout(
						() => hideNotification(id),
						timer
					);
					return { ...notification, timerId: newTimerId }; // Update with new timerId
				}
				return notification;
			})
		);
	};

	return (
		<NotificationContext.Provider
			value={{
				createNotification,
				deleteNotification,
				clearNotifications,
			}}
		>
			<>
				{children}
				<Notification
					onNotificationClick={hideNotification}
					notifications={list}
					handleMouseEnter={handleMouseEnter}
					handleMouseLeave={handleMouseLeave}
				/>
			</>
		</NotificationContext.Provider>
	);
};

export const useNotificationContext = () => {
	return useContext(NotificationContext);
};
