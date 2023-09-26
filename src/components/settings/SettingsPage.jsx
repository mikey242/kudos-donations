/**
 * @see https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
 * @see https://github.com/HardeepAsrani/my-awesome-plugin/
 */

import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { Header } from '../admin/Header';
import { IntroGuide } from './IntroGuide';
import MollieTab from './MollieTab';
import { EmailTab } from './EmailTab';
import { HelpTab } from './HelpTab';
import { Button } from '../controls';
import TabPanel from '../admin/TabPanel';
import { Spinner } from '../Spinner';
// eslint-disable-next-line import/default
import { useSettingsContext } from '../../contexts/SettingsContext';
import classNames from 'classnames';
import { useNotificationContext } from '../../contexts/NotificationContext';
import { ArrowDownTrayIcon } from '@heroicons/react/24/outline';

const SettingsPage = () => {
	const {
		settingsRequest,
		settingsSaving,
		updateSetting,
		updateSettings,
		checkApiKey,
		settings,
		isVendorReady,
	} = useSettingsContext();
	const { createNotification } = useNotificationContext();
	const [showIntro, setShowIntro] = useState(false);
	const methods = useForm({
		defaultValues: settings,
	});

	useEffect(() => {
		if (settings) {
			setShowIntro(settings._kudos_show_intro);
			methods.reset(settings);
		}
	}, [methods, settings]);

	const save = (data) => {
		return updateSettings(data);
	};

	const checkApiKeyWrapper = (keys) => {
		return checkApiKey(keys).then((response) => {
			createNotification(response.message, response?.success);
		});
	};

	// Define tabs and panels
	const tabs = [
		{
			name: 'mollie',
			title: __('Mollie', 'kudos-donations'),
			content: <MollieTab checkApiKeys={checkApiKeyWrapper} />,
		},
		{
			name: 'email',
			title: __('Email', 'kudos-donations'),
			content: <EmailTab />,
		},
		{
			name: 'help',
			title: __('Help', 'kudos-donations'),
			content: <HelpTab setShowIntro={setShowIntro} />,
		},
	];

	return (
		// Show spinner if not yet loaded
		<>
			{!settingsRequest.ready ? (
				<div className="absolute inset-0 flex items-center justify-center">
					<Spinner />
				</div>
			) : (
				<>
					<IntroGuide
						isOpen={showIntro ?? false}
						isAPISaving={settingsSaving}
						setShowIntro={setShowIntro}
						updateSetting={updateSetting}
					/>

					<FormProvider {...methods}>
						<form
							id="settings-form"
							onSubmit={methods.handleSubmit(save)}
						>
							<Header>
								<div className="flex items-center">
									<span
										className={classNames(
											isVendorReady && 'connected',
											'kudos-api-status text-gray-600 capitalize mr-2'
										)}
									>
										{isVendorReady
											? settings._kudos_vendor +
											  ' ' +
											  __('connected', 'kudos-donations')
											: __(
													'Not connected',
													'kudos-donations'
											  )}
									</span>
									<span
										className={classNames(
											isVendorReady
												? 'bg-green-600'
												: 'bg-gray-500',
											'rounded-full inline-block align-middle mr-2 border-2 border-solid border-gray-300 w-4 h-4'
										)}
									/>
									<Button
										type="submit"
										isBusy={settingsSaving}
										icon={
											<ArrowDownTrayIcon className="mr-2 w-5 h-5" />
										}
									>
										{__('Save', 'kudos-donations')}
									</Button>
								</div>
							</Header>
							<TabPanel tabs={tabs} />
						</form>
					</FormProvider>
				</>
			)}
		</>
	);
};

export default SettingsPage;
