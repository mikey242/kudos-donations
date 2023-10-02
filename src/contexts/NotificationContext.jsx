import React from 'react';
import {
	createContext,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
import Notification from '../components/admin/Notification';

export const NotificationContext = createContext(null);

export const NotificationProvider = ({ children }) => {
	const [list, setList] = useState([]);

	const createNotification = (text, success = true) => {
		const id = +new Date();
		setList((prev) => [
			...prev,
			{
				id,
				success,
				text,
			},
		]);
	};

	const deleteNotification = (id) => {
		setList((prev) => prev.filter((el) => el.id !== id));
	};

	const clearNotifications = () => {
		list.forEach(({ timerId }) => clearTimeout(timerId));
		setList([]);
	};

	useEffect(() => {
		list.forEach(({ id }) => {
			setTimeout(() => deleteNotification(id), 3000);
		});
	}, [list]);

	const data = { createNotification, deleteNotification, clearNotifications };

	return (
		<NotificationContext.Provider value={data}>
			<>
				{children}
				<Notification
					onNotificationClick={deleteNotification}
					notifications={list}
				/>
			</>
		</NotificationContext.Provider>
	);
};

export const useNotificationContext = () => {
	return useContext(NotificationContext);
};
