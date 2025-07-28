import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { EmailTab, HelpTab, InvoiceTab, MollieTab } from './tabs';
import { clsx } from 'clsx';
import { AdminTabPanel } from '../AdminTabPanel';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { useAdminContext, useSettingsContext } from '../../contexts';
import { applyFilters } from '@wordpress/hooks';
import type { BaseSettings } from '../../../types/settings';

interface SaveButtonProps {
	isSaving: boolean;
	onClick: () => void;
}

export const SaveButton = ({
	isSaving,
	onClick,
}: SaveButtonProps): React.ReactNode => (
	<Button
		variant="primary"
		type="submit"
		isBusy={isSaving}
		disabled={isSaving}
		onClick={onClick}
	>
		{__('Save', 'kudos-donations')}
	</Button>
);

interface SettingsTab {
	name: string;
	title: string;
	content: React.ReactNode;
}

export const SettingsPage = (): React.ReactNode => {
	const { setHeaderContent } = useAdminContext();
	const { settingsReady, settingsSaving, updateSettings, settings } =
		useSettingsContext();
	const formMethods = useForm({
		defaultValues: settings,
	});
	const { formState } = formMethods;
	const formRef = useRef<HTMLFormElement | null>(null);

	const handleSave = () => {
		formRef.current?.requestSubmit();
	};

	// Define tabs and panels
	const tabs: SettingsTab[] = applyFilters(
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
	) as SettingsTab[];

	useEffect(() => {
		setHeaderContent(
			<>
				<FlexItem>
					<span className="status-text">
						{settings._kudos_payment_vendor_status.ready
							? settings._kudos_payment_vendor_status.text
							: __('not ready', 'kudos-donations')}
					</span>
				</FlexItem>
				<FlexItem>
					<span
						className={clsx(
							settings._kudos_payment_vendor_status.ready
								? 'ready'
								: 'not-ready',
							'status-icon'
						)}
					></span>
				</FlexItem>
				<FlexItem>
					<SaveButton
						isSaving={settingsSaving}
						onClick={handleSave}
					/>
				</FlexItem>
			</>
		);

		return () => {
			setHeaderContent(null);
		};
	}, [
		settings._kudos_payment_vendor_status,
		setHeaderContent,
		settings?._kudos_payment_vendor,
		settingsSaving,
	]);

	useEffect(() => {
		if (settings) {
			formMethods.reset(settings);
		}
	}, [formMethods, settings]);

	const save = (data: BaseSettings): Promise<void> => {
		return updateSettings(data, formState.dirtyFields);
	};

	return (
		<div className="admin-wrap">
			{settingsReady && (
				<FormProvider {...formMethods}>
					<form
						id="settings-form"
						ref={formRef}
						onSubmit={formMethods.handleSubmit(save)}
					>
						<AdminTabPanel tabs={tabs} />
						<Spacer marginTop={'5'} />
						<Flex justify="flex-start">
							<SaveButton
								isSaving={settingsSaving}
								onClick={handleSave}
							/>
						</Flex>
					</form>
				</FormProvider>
			)}
		</div>
	);
};
