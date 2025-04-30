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
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { useAdminContext, useSettingsContext } from '../contexts';
import { applyFilters } from '@wordpress/hooks';

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
	const { setPageTitle } = useAdminContext();

	useEffect(() => {
		setPageTitle(__('Settings', 'kudos-donations'));
	}, [setPageTitle]);

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
		useSettingsContext,
		formMethods
	);

	const SaveButton = () => (
		<Button
			variant="primary"
			type="submit"
			isBusy={settingsSaving}
			disabled={settingsSaving}
			onClick={() => formRef.current?.requestSubmit()}
		>
			{__('Save', 'kudos-donations')}
		</Button>
	);

	useEffect(() => {
		setHeaderContent(
			<>
				<FlexItem>
					<span className="status-text">
						{isVendorReady
							? settings?._kudos_payment_vendor +
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
					<SaveButton />
				</FlexItem>
			</>
		);
	}, [
		isVendorReady,
		setHeaderContent,
		settings?._kudos_payment_vendor,
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
							<Spacer marginTop={'5'} />
							<Flex justify="flex-start">
								<SaveButton />
							</Flex>
						</div>
					</form>
				</FormProvider>
			)}
		</>
	);
};
