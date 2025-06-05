import React, { ReactNode } from 'react';
// @ts-ignore
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
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Flex, Icon, Spinner } from '@wordpress/components';
import { IntroGuide } from '../components';
import type { KudosSettings } from '../../types/settings';
import type { WPErrorResponse } from '../../types/wp';

interface SettingsContextValue {
	settings: KudosSettings;
	setSettings: (newSettings: KudosSettings) => void;
	checkingApiKey: boolean;
	fetchSettings: () => Promise<void>;
	checkApiKey: () => Promise<any>;
	updateSetting: (option: string, value: any) => Promise<any>;
	updateSettings: (
		data: Partial<KudosSettings>,
		dirtyFields?: unknown
	) => Promise<void | any>;
	settingsReady: boolean;
	settingsSaving: boolean;
	isVendorReady: boolean;
}

interface ProviderProps {
	children: ReactNode;
}

const SettingsContext = createContext<SettingsContextValue | null>(null);

export const SettingsProvider = ({ children }: ProviderProps) => {
	const [settingsRequest, setSettingsRequest] = useState<{
		settings: KudosSettings;
		ready: boolean;
	}>({
		ready: false,
		settings: {} as KudosSettings,
	});
	const settingsReady = settingsRequest.ready;
	const { settings } = settingsRequest;
	const [isVendorReady, setIsVendorReady] = useState<boolean>(false);
	const [checkingApiKey, setCheckingApiKey] = useState<boolean>(false);
	const [settingsSaving, setSettingsSaving] = useState<boolean>(false);
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);

	useEffect(() => {
		apiFetch({
			path: '/kudos/v1/payment/ready',
			method: 'GET',
		}).then((r: boolean) => setIsVendorReady(r));
	}, [settings]);

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
			settings: filteredSettings as KudosSettings,
		});
	};

	useEffect(() => {
		void fetchSettings();
	}, []);

	const setSettings = (newSettings: KudosSettings) => {
		setSettingsRequest((prevState) => {
			return {
				...prevState,
				ready: true,
				settings: { ...newSettings },
			};
		});
	};

	// Update all settings.
	async function updateSettings(
		data: KudosSettings,
		dirtyFields = null
	): Promise<void> {
		// If dirty fields have been specified, then filter out unchanged data.
		if (dirtyFields) {
			data = dirtyValues(dirtyFields, data);
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
			.then(async (response: KudosSettings) => {
				return new Promise<void>((resolve) => {
					setTimeout(() => {
						createSuccessNotice(
							__('Settings updated', 'kudos-donations'),
							{ type: 'snackbar', icon: <Icon icon="saved" /> }
						);
						setSettings(response);
						resolve();
					}, 500);
				});
			})
			.catch((error: WPErrorResponse) => {
				createErrorNotice(error?.message, {
					type: 'snackbar',
				});
			})
			.always(() => {
				setSettingsSaving(false);
			});
	}

	// Update an individual setting, uses current state if value not specified.
	async function updateSetting(
		option: keyof KudosSettings,
		value: KudosSettings[keyof KudosSettings]
	): Promise<KudosSettings> {
		// Create WordPress settings model.
		const model = new api.models.Settings({
			[option]: value,
		});

		// Save to database.
		return model.save().then((response: KudosSettings) => {
			setSettings(response);
			return response;
		});
	}

	async function checkApiKey(): Promise<any> {
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
	const dirtyValues = (
		dirtyFields: unknown,
		allValues: KudosSettings
	): KudosSettings => {
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
				fetchSettings,
				checkApiKey,
				updateSetting,
				updateSettings,
				settingsReady,
				settingsSaving,
				isVendorReady,
			}}
		>
			{settingsReady ? (
				<>
					<IntroGuide />
					{children}
				</>
			) : (
				<Flex justify="center">
					<Spinner />
				</Flex>
			)}
		</SettingsContext.Provider>
	);
};

export const useSettingsContext = (): SettingsContextValue => {
	const context = useContext(SettingsContext);
	if (!context) {
		throw new Error(
			'useSettingsContext must be used within a SettingsProvider'
		);
	}
	return context;
};
