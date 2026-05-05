import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import {
	ApiModePanel,
	PaymentMethodsPanel,
	ApiKeysPanel,
	EmailReceiptsPanel,
	SmtpSettingsPanel,
	TestEmailPanel,
	YourDetailsPanel,
	OtherPanel,
	AddonPanel,
	LicenceKeyPanel,
	ShareTheLovePanel,
	AboutPanel,
	AdvancedPanel,
} from './tabs';
import { clsx } from 'clsx';
import { AdminTabPanel } from '../AdminTabPanel';
import type { AdminTab } from '../AdminTabPanel';
import { Button, Fill, Flex, FlexItem } from '@wordpress/components';
import { Spacer } from '../../components';
import { useSettingsContext } from '../../contexts';
import { SLOT_HEADER_ACTIONS } from '../../slot-names';
import { applyFilters } from '@wordpress/hooks';
import type { AllSettings } from '../../../types/all-settings';
import { isLicenceActive } from '../../../utils/licence';

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

	const tabs: AdminTab[] = applyFilters('kudosSettingsTabs', [
		{
			name: 'mollie',
			title: __('Mollie', 'kudos-donations'),
			panels: [
				{ name: 'api-mode', content: <ApiModePanel /> },
				{ name: 'payment-methods', content: <PaymentMethodsPanel /> },
				{ name: 'api-keys', content: <ApiKeysPanel /> },
			],
		},
		{
			name: 'email',
			title: __('Email', 'kudos-donations'),
			panels: [
				{ name: 'email-receipts', content: <EmailReceiptsPanel /> },
				{ name: 'smtp-settings', content: <SmtpSettingsPanel /> },
				{ name: 'test-email', content: <TestEmailPanel /> },
			],
		},
		{
			name: 'receipt',
			title: __('Receipt', 'kudos-donations'),
			panels: [
				{ name: 'your-details', content: <YourDetailsPanel /> },
				{ name: 'other', content: <OtherPanel /> },
			],
		},
		{
			name: 'plus',
			title: 'Plus',
			panels: [
				{ name: 'addon', content: <AddonPanel /> },
				{ name: 'licence-key', content: <LicenceKeyPanel /> },
			],
		},
		{
			name: 'help',
			title: __('Help', 'kudos-donations'),
			panels: [
				{ name: 'share-the-love', content: <ShareTheLovePanel /> },
				{ name: 'about', content: <AboutPanel /> },
				{ name: 'advanced', content: <AdvancedPanel /> },
			],
		},
	]) as AdminTab[];

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
								{isLicenceActive(
									settings._kudos_licence_status
								) && __('licence active', 'kudos-donations')}
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
						<Spacer size={5} />
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
