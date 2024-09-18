/**
 * @see https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
 * @see https://github.com/HardeepAsrani/my-awesome-plugin/
 */

import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { AdminHeader } from '../AdminHeader';
import MollieTab from './MollieTab';
import { EmailTab } from './EmailTab';
import { HelpTab } from './HelpTab';
// eslint-disable-next-line import/default
import { useSettingsContext } from '../../contexts/SettingsContext';
import { clsx } from 'clsx';
import { InvoiceTab } from './InvoiceTab';
import { AdminTabPanel } from '../AdminTabPanel';
import { Button, FlexItem } from '@wordpress/components';
import { useAdminContext } from '../../contexts/AdminContext';

export const SettingsPage = () => {
	const { setHeaderContent } = useAdminContext();
	const {
		settingsReady,
		settingsSaving,
		updateSettings,
		settings,
		isVendorReady,
	} = useSettingsContext();
	const methods = useForm({
		defaultValues: settings,
	});
	const { formState } = methods;

	useEffect(() => {
		setHeaderContent(
			<>
				<FlexItem>
					<span className="status-text">
						{isVendorReady
							? settings?._kudos_vendor +
								' ' +
								__('ready', 'kudos-donations')
							: __('not ready', 'kudos-donations')}
					</span>
				</FlexItem>
				<FlexItem>
					<span
						className={clsx(
							isVendorReady ? 'ready' : 'not-ready',
							'status-icon'
						)}
					></span>
				</FlexItem>
				<FlexItem>
					<Button
						variant="primary"
						type="submit"
						isBusy={settingsSaving}
					>
						{__('Save', 'kudos-donations')}
					</Button>
				</FlexItem>
			</>
		);
	}, [
		isVendorReady,
		setHeaderContent,
		settings?._kudos_vendor,
		settingsSaving,
	]);

	useEffect(() => {
		if (settings) {
			methods.reset(settings);
		}
	}, [methods, settings]);

	const save = (data) => {
		return updateSettings(data, formState.dirtyFields);
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
			name: 'invoice',
			title: __('Invoice', 'kudos-donations'),
			content: <InvoiceTab />,
		},
		{
			name: 'help',
			title: __('Help', 'kudos-donations'),
			content: <HelpTab />,
		},
	];

	return (
		// Show spinner if not yet loaded
		<>
			{settingsReady && (
				<FormProvider {...methods}>
					<form
						id="settings-form"
						onSubmit={methods.handleSubmit(save)}
					>
						<AdminHeader />
						<div className="admin-wrap">
							<AdminTabPanel tabs={tabs} />
						</div>
					</form>
				</FormProvider>
			)}
		</>
	);
};
