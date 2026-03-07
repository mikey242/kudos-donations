import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { EmailTab, HelpTab, ReceiptTab, MollieTab, LicenceTab } from './tabs';
import { clsx } from 'clsx';
import { AdminTabPanel } from '../AdminTabPanel';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Fill,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { useSettingsContext } from '../../contexts';
import { SLOT_HEADER_ACTIONS } from '../AdminHeader';
import { applyFilters } from '@wordpress/hooks';
import type { AllSettings } from '../../../types/all-settings';
import { isLicenceActive } from '../../utils';

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
	const { settingsReady, settingsSaving, updateSettings, settings } =
		useSettingsContext<AllSettings>();
	const formMethods = useForm({
		defaultValues: settings,
	});
	const { formState } = formMethods;
	const formRef = useRef<HTMLFormElement | null>(null);

	const handleSave = () => {
		formRef.current?.requestSubmit();
	};

	// Define tabs and panels
	const tabs: SettingsTab[] = applyFilters('kudosSettingsTabs', [
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
			name: 'receipt',
			title: __('Receipt', 'kudos-donations'),
			content: <ReceiptTab />,
		},
		{
			name: 'licence',
			title: 'Licence',
			content: <LicenceTab />,
		},
		{
			name: 'help',
			title: __('Help', 'kudos-donations'),
			content: <HelpTab />,
		},
	]) as SettingsTab[];

	useEffect(() => {
		if (settings) {
			formMethods.reset(settings);
		}
	}, [formMethods, settings]);

	const save = (data: AllSettings): Promise<void> => {
		return updateSettings(data, formState.dirtyFields);
	};

	return (
		<div className="admin-wrap">
			{settingsReady && (
				<FormProvider {...formMethods}>
					<Fill name={SLOT_HEADER_ACTIONS}>
						<FlexItem>
							<span className="status-text">
								{isLicenceActive(settings._kudos_licence_status)
									? __('licence active', 'kudos-donations')
									: __('free version', 'kudos-donations')}
							</span>
						</FlexItem>
						<FlexItem>
							<span
								className={clsx(
									isLicenceActive(
										settings._kudos_licence_status
									)
										? 'ready status-icon'
										: 'not-ready'
								)}
							></span>
						</FlexItem>
						<FlexItem>
							<SaveButton
								isSaving={settingsSaving}
								onClick={handleSave}
							/>
						</FlexItem>
					</Fill>
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
