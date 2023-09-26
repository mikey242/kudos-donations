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
		const timerId = setTimeout(deleteNotification, 3000, id);
		setList((prev) =>
			prev.concat({
				id,
				timerId,
				success,
				text,
			})
		);
	};

	const deleteNotification = (id) => {
		setList((prev) => prev.filter((el) => el.id !== id));
	};

	const clearNotifications = () => {
		setList([]);
	};

	useEffect(() => {
		return () => list.forEach(({ timerId }) => clearTimeout(timerId));
	}, [list]);

	const data = { createNotification, deleteNotification, clearNotifications };

	return (
		<NotificationContext.Provider value={data}>
			<>
				{children}
				<Notification notifications={list} />
			</>
		</NotificationContext.Provider>
	);
};

export const useNotificationContext = () => {
	return useContext(NotificationContext);
};
