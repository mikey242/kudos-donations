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

	useEffect(() => {
		if (settingsRequest.ready) {
			const vendor = settings._kudos_vendor;
			if (vendor) {
				const mode = settings[`_kudos_vendor_${vendor}_api_mode`];
				if (mode) {
					setIsVendorReady(
						settings[`_kudos_vendor_${vendor}_api_key_${mode}`]
					);
				}
			}
		}
	}, [settings, settingsRequest.ready]);

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
		void fetchSettings();
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

	// Update all settings.
	async function updateSettings(data, dirtyFields = null) {
		// If dirty fields have been specified, then filter out unchanged data.
		if (dirtyFields) {
			data = dirtyValues(dirtyFields, data);
		}

		// Nothing changed, no need to continue.
		if (!Object.keys(data).length) {
			return createNotification(__('Nothing changed', 'kudos-donations'));
		}

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
				createNotification(error?.responseJSON.data, false);
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

	async function checkApiKey() {
		setCheckingApiKey(true);
		return apiFetch({
			path: 'kudos/v1/payment/test',
			method: 'POST',
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

	// @see https://github.com/orgs/react-hook-form/discussions/1991#discussioncomment-31308
	const dirtyValues = (dirtyFields, allValues) => {
		// If dirtyFields is true or an array, return the entire allValues
		if (dirtyFields === true || Array.isArray(dirtyFields)) {
			return allValues;
		}

		// Process object to get modified fields
		return Object.fromEntries(
			Object.entries(dirtyFields)
				.map(([key, value]) => {
					// Check if value is an object
					if (
						value &&
						typeof value === 'object' &&
						!Array.isArray(value)
					) {
						// Recursively get dirty fields
						const nestedDirty = dirtyValues(value, allValues[key]);
						// Return entire object if any nested field is dirty
						return nestedDirty !== undefined
							? [key, allValues[key]]
							: undefined;
					}
					// Return value if the field itself is dirty
					return value === true ? [key, allValues[key]] : undefined;
				})
				.filter((entry) => entry !== undefined) // Filter out undefined entries
		);
	};

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
