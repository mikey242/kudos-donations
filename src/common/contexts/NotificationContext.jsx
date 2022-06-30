import React from 'react';
import {
	createContext,
	useContext,
	useEffect,
	useReducer,
	useRef,
} from '@wordpress/element';
import Notification from '../components/Notification';

export const NotificationContext = createContext(null);

const initialState = [];
const init = (initialState) => {
	return initialState;
};

export const ADD = 'ADD';
export const REMOVE = 'REMOVE';
export const REMOVE_ALL = 'REMOVE_ALL';

export const notificationReducer = (state, action) => {
	switch (action.type) {
		case ADD:
			return [
				...state,
				{
					id: +new Date(),
					content: action.payload.content,
					success: action.payload.success,
				},
			];
		case REMOVE:
			return state.filter((t) => t.id !== action.payload.id);
		case REMOVE_ALL:
			return initialState;
		default:
			return state;
	}
};

export const NotificationProvider = ({ children }) => {
	const notificationTimer = useRef(null);
	const [notifications, notificationDispatch] = useReducer(
		notificationReducer,
		initialState,
		init
	);
	const notificationData = { notifications, notificationDispatch };

	useEffect(() => {
		if (notifications.length) {
			notificationTimer.current = setTimeout(() => {
				return notificationDispatch({
					type: REMOVE,
					payload: {
						id: notifications?.[0].id,
					},
				});
			}, 2000);
			return () => clearTimeout(notificationTimer.current);
		}
	}, [notifications]);

	return (
		<NotificationContext.Provider value={notificationData}>
			<>
				{children}
				<Notification notifications={notifications} />
			</>
		</NotificationContext.Provider>
	);
};

export const useNotificationContext = () => {
	return useContext(NotificationContext);
};
