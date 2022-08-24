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
		settings: null,
	});
	const [settingsReady] = useState(false);
	const [isVendorConnected, setIsVendorConnected] = useState(false);
	const [checkingApiKey, setCheckingApiKey] = useState(false);
	const [settingsSaving, setSettingsSaving] = useState(false);
	const { settings } = settingsRequest;
	const { createNotification } = useNotificationContext();

	useEffect(() => {
		getSettings();
	}, []);

	useEffect(() => {
		if (settings) {
			const vendor = settings._kudos_vendor;
			const vendorSetting = settings[`_kudos_vendor_${vendor}`];
			setIsVendorConnected(vendorSetting.connected);
		}
	}, [settings]);

	const setSettings = (newSettings) => {
		setSettingsRequest((prev) => {
			return {
				...prev,
				settings: { ...newSettings },
			};
		});
	};

	const getSettings = () => {
		return api.loadPromise.then(() => {
			const settingsModel = new api.models.Settings();
			return settingsModel.fetch().then((response) => {
				setSettingsRequest({
					ready: true,
					settings: response,
				});
				return response;
			});
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
		return model.save().then(async (response) => {
			createNotification(__('Settings updated', 'kudos-donations'), true);
			setTimeout(() => {
				setSettingsSaving(false);
			}, 500);
			setSettings(response);
			return response;
		});
	}

	// Update an individual setting, uses current state if value not specified.
	async function updateSetting(option, value) {
		setSettingsSaving(true);

		// Create WordPress settings model.
		const model = new api.models.Settings({
			[option]: value,
		});

		// Save to database.
		return model.save().then((response) => {
			setSettings(response);
			setSettingsSaving(false);
			return response;
		});
	}

	async function checkApiKey(keys) {
		setCheckingApiKey(true);
		return apiFetch({
			path: 'kudos/v1/payment/test',
			method: 'POST',
			data: keys,
		})
			.then((response) => {
				return response;
			})
			.finally(() => {
				getSettings();
				setCheckingApiKey(false);
			});
	}

	return (
		<SettingsContext.Provider
			value={{
				settings,
				settingsRequest,
				setSettings,
				checkingApiKey,
				checkApiKey,
				updateSetting,
				updateSettings,
				settingsReady,
				settingsSaving,
				setIsVendorConnected,
				isVendorConnected,
			}}
		>
			{children}
		</SettingsContext.Provider>
	);
}

export const useSettingsContext = () => {
	return useContext(SettingsContext);
};
