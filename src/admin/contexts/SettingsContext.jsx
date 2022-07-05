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
	const [settingsReady, setSettingsReady] = useState(false);
	const [settingsSaving, setSettingsSaving] = useState(false);
	const [settings, setSettings] = useState();
	const { createNotification } = useNotificationContext();

	useEffect(() => {
		getSettings();
	}, []);

	useEffect(() => {
		if (settings) {
			setSettingsReady(true);
		}
	}, [settings]);

	const getSettings = () => {
		return api.loadPromise.then(() => {
			const settingsModel = new api.models.Settings();
			settingsModel.fetch().then((response) => {
				if (response._kudos_show_intro) {
					window.location.replace('admin.php?page=kudos-settings');
				} else {
					setSettings(response);
				}
			});
		});
	};

	// Update all settings
	async function updateSettings(data) {
		setSettingsSaving(true);

		// Delete empty settings keys
		for (const key in data) {
			if (data[key] === null) {
				delete data[key];
			}
		}

		// Create WordPress settings model
		const model = new api.models.Settings(data);

		// Save to database
		return model.save().then(async (response) => {
			createNotification(__('Settings updated', 'kudos-donations'), true);
			setSettingsSaving(false);
			setSettings(response);
			return response;
		});
	}

	// Update an individual setting, uses current state if value not specified
	async function updateSetting(option, value) {
		setSettingsSaving(true);

		// Create WordPress settings model
		const model = new api.models.Settings({
			[option]: value,
		});

		// Save to database
		return model.save().then((response) => {
			setSettings(response);
			setSettingsSaving(false);
			return response;
		});
	}

	async function checkApiKey(keys) {
		return apiFetch({
			path: 'kudos/v1/payment/test',
			method: 'POST',
			data: keys,
		}).then((response) => {
			updateSetting('_kudos_vendor_mollie.connected', response?.success);
			return response;
		});
	}

	return (
		<SettingsContext.Provider
			value={{
				settings,
				setSettings,
				checkApiKey,
				updateSetting,
				updateSettings,
				settingsReady,
				settingsSaving,
			}}
		>
			{children}
		</SettingsContext.Provider>
	);
}

export const useSettingsContext = () => {
	return useContext(SettingsContext);
};
