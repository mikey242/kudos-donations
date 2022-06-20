/**
 * @see https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
 * @see https://github.com/HardeepAsrani/my-awesome-plugin/
 */

import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import React from 'react';
import api from '@wordpress/api';
import { FormProvider, useForm } from 'react-hook-form';

// settings Panels
import { Header } from '../Header';
import { IntroGuide } from '../IntroGuide';
import MollieTab from './tabs/MollieTab';
import { EmailTab } from './tabs/EmailTab';
import { HelpTab } from './tabs/HelpTab';
import { Button } from '../../../common/components/controls';
import Notification from '../Notification';
import Render from '../../../common/components/Render';
import TabPanel from '../TabPanel';
import { fetchTestMollie } from '../../../common/helpers/fetch';
import { Spinner } from '../../../common/components/Spinner';

const KudosSettings = ({ stylesheet }) => {
	const [isAPISaving, setIsAPISaving] = useState(false);
	const [isAPILoaded, setIsAPILoaded] = useState(false);
	const [settings, setSettings] = useState();
	const [showIntro, setShowIntro] = useState(false);
	const [notification, setNotification] = useState({ shown: false });
	const notificationTimer = useRef(null);
	const methods = useForm({
		defaultValues: settings,
	});
	const { dirtyFields } = methods.formState;

	useEffect(() => {
		if (notification.shown) {
			notificationTimer.current = setTimeout(() => {
				hideNotification();
			}, 2000);
			return () => clearTimeout(notificationTimer.current);
		}
	});

	useEffect(() => {
		if (settings) {
			setIsAPILoaded(true);
			methods.reset(settings);
		}
	}, [settings]);

	// Returns an object with only _kudos prefixed settings
	const filterSettings = (settings) => {
		return Object.fromEntries(
			Object.entries(settings).filter(([key]) => key.startsWith('_kudos'))
		);
	};

	const getSettings = () => {
		api.loadPromise.then(() => {
			const settings = new api.models.Settings();
			settings
				.fetch()
				.then((response) => filterSettings(response))
				.then((response) => {
					setShowIntro(response._kudos_show_intro);
					setSettings(response);
				});
		});
	};

	const createNotification = (message, success) => {
		setNotification({
			message,
			success,
			shown: true,
		});
	};

	const hideNotification = () => {
		setNotification((prev) => ({
			...prev,
			shown: false,
		}));
	};

	// Update all settings
	async function updateSettings(data) {
		setIsAPISaving(true);

		// Delete empty settings keys
		for (const key in data) {
			if (data[key] === null) {
				delete data[key];
			}
		}

		// Create WordPress settings model
		const model = new api.models.Settings(data);

		// Save to database
		return model
			.save()
			.then(async (response) => {
				setSettings(filterSettings(response));
				setIsAPISaving(false);
				createNotification(__('Settings updated', 'kudos-donations'));
				if ('_kudos_vendor_' + settings._kudos_vendor in dirtyFields) {
					await checkApiKey();
				}
			})
			.fail(() => {
				createNotification(
					__('Failed to save settings', 'kudos-donations'),
					false
				);
			});
	}

	// Update an individual setting, uses current state if value not specified
	async function updateSetting(option, value) {
		setIsAPISaving(true);

		// Create WordPress settings model
		const model = new api.models.Settings({
			[option]: value,
		});

		// Save to database
		return model.save().then((response) => {
			setSettings(filterSettings(response));
			setIsAPISaving(false);
		});
	}

	async function checkApiKey() {
		return fetchTestMollie().then((response) => {
			createNotification(response.data.message, response?.success);
			updateSetting('_kudos_vendor_mollie.connected', response?.success);
			return response;
		});
	}

	// Define tabs and panels
	const tabs = [
		{
			name: 'mollie',
			title: __('Mollie', 'kudos-donations'),
			content: <MollieTab checkApiKey={checkApiKey} />,
		},
		{
			name: 'email',
			title: __('Email', 'kudos-donations'),
			content: <EmailTab createNotification={createNotification} />,
		},
		{
			name: 'help',
			title: __('Help', 'kudos-donations'),
			content: <HelpTab setShowIntro={setShowIntro} />,
		},
	];

	return (
		// Show spinner if not yet loaded
		<Render stylesheet={stylesheet.href}>
			{!isAPILoaded ? (
				<div className="absolute inset-0 flex items-center justify-center">
					<Spinner />
				</div>
			) : (
				<FormProvider {...methods}>
					<form
						id="settings-form"
						onSubmit={methods.handleSubmit(updateSettings)}
					>
						{showIntro ? (
							<IntroGuide
								updateSettings={updateSettings}
								checkApiKey={checkApiKey}
								isAPISaving={isAPISaving}
								settings={settings}
								setShowIntro={setShowIntro}
								updateSetting={updateSetting}
							/>
						) : (
							''
						)}

						<Header>
							<div className="flex items-center">
								<span
									className={`${
										settings._kudos_vendor_mollie
											.connected && 'connected'
									} kudos-api-status text-gray-600 capitalize mr-2`}
								>
									{settings?.[
										'_kudos_vendor_' +
											settings._kudos_vendor
									].connected
										? settings._kudos_vendor +
										  ' ' +
										  __('connected', 'kudos-donations')
										: __(
												'Not connected',
												'kudos-donations'
										  )}
								</span>
								<span
									className={`${
										settings._kudos_vendor_mollie.connected
											? 'bg-green-600'
											: 'bg-gray-500'
									} rounded-full inline-block align-middle mr-2 border-2 border-solid border-gray-300 w-4 h-4`}
								/>
								<Button form="settings-form" type="submit">
									{__('Save', 'kudos-donations')}
								</Button>
							</div>
						</Header>
						<TabPanel tabs={tabs} />
						<Notification
							shown={notification.shown}
							message={notification.message}
							success={notification.success}
							onClick={hideNotification}
						/>
					</form>
				</FormProvider>
			)}
		</Render>
	);
};

export default KudosSettings;
