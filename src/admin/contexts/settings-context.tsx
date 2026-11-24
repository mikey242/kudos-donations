import React, { ReactNode } from 'react';
// @ts-ignore
import api from '@wordpress/api';
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Flex, Spinner } from '@wordpress/components';
import type { BaseSettings } from '../../types/settings';
import type { WPErrorResponse } from '../../types/wp';
import { dirtyValues } from '../../utils';
import { isLicenceActive } from '../utils';
import { doAction } from '@wordpress/hooks';

interface SettingsContextValue<T extends BaseSettings> {
	settings: T;
	isLicenceActive: boolean;
	checkingApiKey: boolean;
	fetchSettings: () => Promise<void>;
	checkApiKey: () => Promise<any>;
	updateSetting: <K extends keyof T>(option: K, value: T[K]) => Promise<T>;
	updateSettings: (
		data: Partial<T>,
		dirtyFields?: unknown
	) => Promise<void | any>;
	settingsReady: boolean;
	settingsSaving: boolean;
}

interface ProviderProps {
	children: ReactNode;
}

const SettingsContext = createContext<any | null>(null);

export const SettingsProvider = <T extends BaseSettings>({
	children,
}: ProviderProps) => {
	const [settingsRequest, setSettingsRequest] = useState<{
		settings: T;
		ready: boolean;
	}>({
		ready: false,
		settings: {} as T,
	});
	const settingsReady = settingsRequest.ready;
	const { settings } = settingsRequest;
	const [checkingApiKey, setCheckingApiKey] = useState<boolean>(false);
	const [settingsSaving, setSettingsSaving] = useState<boolean>(false);
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);

	const fetchSettings = useCallback(async () => {
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
			settings: filteredSettings as T,
		});
	}, []);

	useEffect(() => {
		void fetchSettings();
	}, [fetchSettings]);

	const setSettings = useCallback((newSettings: T) => {
		setSettingsRequest((prevState) => {
			return {
				...prevState,
				ready: true,
				settings: { ...newSettings },
			};
		});
	}, []);

	// Update all settings.
	const updateSettings = useCallback(
		async (data: T, dirtyFields = null): Promise<void> => {
			const values: Record<string, any> =
				dirtyFields && Object.keys(dirtyFields as object).length > 0
					? dirtyValues(dirtyFields, data as Record<string, any>)
					: data;

			// Leave out null settings. Build a copy rather than deleting keys, as the values
			// passed in are usually the form's own. Empty strings are still sent: they are how
			// a setting is cleared, e.g. resetting an API key.
			const payload = Object.fromEntries(
				Object.entries(values).filter(([, value]) => value !== null)
			);

			setSettingsSaving(true);

			// Create WordPress settings model.
			const model = new api.models.Settings(payload);

			// Save to database.
			return model
				.save()
				.then((response: T) => {
					createSuccessNotice(
						__('Settings updated', 'kudos-donations'),
						{ type: 'snackbar', icon: '✅' }
					);
					setSettings(response);
					doAction('kudos_settings_saved', response);
				})
				.catch((error: WPErrorResponse) => {
					createErrorNotice(error?.message, {
						type: 'snackbar',
					});
				})
				.always(() => {
					setSettingsSaving(false);
				});
		},
		[createErrorNotice, createSuccessNotice, setSettings]
	);

	// Update an individual setting.
	const updateSetting = useCallback(
		async (option: keyof T, value: T[keyof T]): Promise<T> => {
			const model = new api.models.Settings({ [option]: value });
			return model.save().then((response: T) => {
				setSettings(response);
				doAction('kudos_settings_saved', response);
				return response;
			});
		},
		[setSettings]
	);

	const licenceActive = isLicenceActive(
		(settings as any)._kudos_licence_status ?? {}
	);

	const checkApiKey = useCallback(async (): Promise<any> => {
		setCheckingApiKey(true);
		try {
			return await apiFetch({
				path: 'kudos/v1/payment/test',
				method: 'POST',
			});
		} catch (response) {
			return response;
		} finally {
			await fetchSettings();
			setCheckingApiKey(false);
		}
	}, [fetchSettings]);

	const contextValue = useMemo(
		() => ({
			settings,
			isLicenceActive: licenceActive,
			checkingApiKey,
			checkApiKey,
			fetchSettings,
			updateSetting,
			updateSettings,
			settingsReady,
			settingsSaving,
		}),
		[
			settings,
			licenceActive,
			checkingApiKey,
			checkApiKey,
			fetchSettings,
			updateSetting,
			updateSettings,
			settingsReady,
			settingsSaving,
		]
	);

	return (
		<SettingsContext.Provider value={contextValue}>
			{settingsReady ? (
				children
			) : (
				<Flex justify="center">
					<Spinner />
				</Flex>
			)}
		</SettingsContext.Provider>
	);
};

export const useSettingsContext = <
	T extends BaseSettings,
>(): SettingsContextValue<T> => {
	const context = useContext(SettingsContext);
	if (!context) {
		throw new Error(
			'useSettingsContext must be used within a SettingsProvider'
		);
	}
	return context as SettingsContextValue<T>;
};
