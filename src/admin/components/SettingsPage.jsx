/**
 * @see https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
 * @see https://github.com/HardeepAsrani/my-awesome-plugin/
 */

import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { Header } from './Header';
import { IntroGuide } from './IntroGuide';
import MollieTab from './MollieTab';
import { EmailTab } from './EmailTab';
import { HelpTab } from './HelpTab';
import { Button } from '../../common/components/controls';
import TabPanel from './TabPanel';
import { Spinner } from '../../common/components/Spinner';
// eslint-disable-next-line import/default
import { useSettingsContext } from '../contexts/SettingsContext';
import { SaveIcon } from '@heroicons/react/outline';
import classNames from 'classnames';

const SettingsPage = () => {
	const [showIntro, setShowIntro] = useState(false);
	const {
		settingsRequest,
		settingsSaving,
		updateSetting,
		updateSettings,
		checkApiKey,
		settings,
		isVendorConnected,
	} = useSettingsContext();
	const methods = useForm({
		defaultValues: settings,
	});
	const { dirtyFields } = methods.formState;

	useEffect(() => {
		if (settings) {
			methods.reset(settings);
		}
	}, [settings]);

	const save = (data) => {
		updateSettings(data).then(async () => {
			if (`_kudos_vendor_${settings._kudos_vendor}` in dirtyFields) {
				await checkApiKey({
					keys: methods.getValues(
						`_kudos_vendor_${settings._kudos_vendor}`
					),
				});
			}
		});
	};

	// Define tabs and panels
	const tabs = [
		{
			name: 'mollie',
			title: __('Mollie', 'kudos-donations'),
			content: <MollieTab />,
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
											isVendorConnected && 'connected',
											'kudos-api-status text-gray-600 capitalize mr-2'
										)}
									>
										{isVendorConnected
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
											isVendorConnected
												? 'bg-green-600'
												: 'bg-gray-500',
											'rounded-full inline-block align-middle mr-2 border-2 border-solid border-gray-300 w-4 h-4'
										)}
									/>
									<Button
										type="submit"
										isBusy={settingsSaving}
										icon={
											<SaveIcon className="mr-2 w-5 h-5" />
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
