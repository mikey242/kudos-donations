/**
 * @see https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
 * @see https://github.com/HardeepAsrani/my-awesome-plugin/
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { MollieTab, EmailTab, HelpTab, InvoiceTab } from './tabs';
import { clsx } from 'clsx';
import { AdminTabPanel } from '../AdminTabPanel';
import { Button, FlexItem } from '@wordpress/components';
import { useAdminContext, useSettingsContext } from '../contexts';
import { applyFilters } from '@wordpress/hooks';
import * as AdminControls from '../../components/controls';

export const SettingsPage = () => {
	const { setHeaderContent } = useAdminContext();
	const {
		settingsReady,
		settingsSaving,
		updateSettings,
		settings,
		isVendorReady,
	} = useSettingsContext();
	const formMethods = useForm({
		defaultValues: settings,
	});
	const { formState } = formMethods;
	const formRef = useRef(null);

	// Define tabs and panels
	const tabs = applyFilters(
		'kudosSettingsTabs',
		[
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
		],
		AdminControls,
		useSettingsContext,
		formMethods
	);

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
						disabled={settingsSaving}
						onClick={() => formRef.current?.requestSubmit()}
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
			formMethods.reset(settings);
		}
	}, [formMethods, settings]);

	const save = (data) => {
		return updateSettings(data, formState.dirtyFields);
	};

	return (
		// Show spinner if not yet loaded
		<>
			{settingsReady && (
				<FormProvider {...formMethods}>
					<form
						id="settings-form"
						ref={formRef}
						onSubmit={formMethods.handleSubmit(save)}
					>
						<div className="admin-wrap">
							<AdminTabPanel tabs={tabs} />
						</div>
					</form>
				</FormProvider>
			)}
		</>
	);
};
