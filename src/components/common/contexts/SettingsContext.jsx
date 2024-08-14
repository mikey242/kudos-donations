import React from 'react';
import api from '@wordpress/api';
import {
	createContext,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useNotificationContext } from './NotificationContext';

export const SettingsContext = createContext(null);

export default function SettingsProvider({ children }) {
	const [settingsRequest, setSettingsRequest] = useState({
		ready: false,
		settings: {},
	});
	const settingsReady = settingsRequest.ready;
	const { settings } = settingsRequest;
	const [isVendorReady, setIsVendorReady] = useState(false);
	const [checkingApiKey, setCheckingApiKey] = useState(false);
	const [settingsSaving, setSettingsSaving] = useState(false);
	const { createNotification } = useNotificationContext();

	const fetchSettings = async () => {
		await api.loadPromise;
		const settingsModel = new api.models.Settings();
		const allSettings = await settingsModel.fetch();
		const filteredSettings = Object.keys(allSettings)
			.filter((key) => key.startsWith('_kudos_'))
			.reduce((obj, key) => {
				obj[key] = allSettings[key];
				return obj;
			}, {});
		setSettingsRequest({
			ready: true,
			settings: filteredSettings,
		});
	};

	useEffect(() => {
		fetchSettings().then(getVendorStatus);
	}, []);

	const setSettings = (newSettings) => {
		setSettingsRequest((prevState) => {
			return {
				...prevState,
				ready: true,
				settings: { ...newSettings },
			};
		});
	};

	const getVendorStatus = () => {
		return apiFetch({
			path: 'kudos/v1/payment/ready',
			method: 'GET',
		})
			.then((response) => {
				setIsVendorReady(response);
				return response;
			})
			.catch((response) => {
				return response;
			});
	};

	// Update all settings.
	async function updateSettings(data) {
		setSettingsSaving(true);
		// Delete empty settings keys.
		for (const key in data) {
			if (data[key] === null) {
				delete data[key];
			}
		}

		// Create WordPress settings model.
		const model = new api.models.Settings(data);

		// Save to database.
		return model
			.save()
			.then(async (response) => {
				return new Promise((resolve) => {
					setTimeout(() => {
						createNotification(
							__('Settings updated', 'kudos-donations'),
							true
						);
						setSettings(response);
						resolve();
					}, 500);
				});
			})
			.catch((error) => {
				createNotification(error?.responseJSON.message, false);
			})
			.always(() => {
				setSettingsSaving(false);
			});
	}

	// Update an individual setting, uses current state if value not specified.
	async function updateSetting(option, value) {
		// Create WordPress settings model.
		const model = new api.models.Settings({
			[option]: value,
		});

		// Save to database.
		return model.save().then((response) => {
			setSettings(response);
			return response;
		});
	}

	async function checkApiKey(keys) {
		setCheckingApiKey(true);
		return apiFetch({
			path: 'kudos/v1/payment/test',
			method: 'POST',
			data: { keys },
		})
			.then((response) => {
				return response;
			})
			.catch((response) => {
				return response;
			})
			.finally(() => {
				fetchSettings();
				setCheckingApiKey(false);
			});
	}

	return (
		<SettingsContext.Provider
			value={{
				settings,
				setSettings,
				checkingApiKey,
				checkApiKey,
				updateSetting,
				updateSettings,
				settingsReady,
				settingsSaving,
				setIsVendorReady,
				isVendorReady,
			}}
		>
			{children}
		</SettingsContext.Provider>
	);
}

export const useSettingsContext = () => {
	return useContext(SettingsContext);
};
