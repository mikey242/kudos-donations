import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { usePaymentTab, EmailTab, ReceiptTab, PlusTab, HelpTab } from './tabs';
import { clsx } from 'clsx';
import { AdminTabPanel } from '../AdminTabPanel';
import type { AdminTab } from '../AdminTabPanel';
import { Fill, FlexItem } from '@wordpress/components';
import { StickySaveBar } from '../../components';
import { useSettingsContext } from '../../contexts';
import { usePageTitle } from '../../hooks';
import { SLOT_HEADER_ACTIONS } from '../../slot-names';
import { applyFilters } from '@wordpress/hooks';
import type { AllSettings } from '../../../types/all-settings';

export const SettingsPage = (): React.ReactNode => {
	const {
		settingsReady,
		settingsSaving,
		updateSettings,
		settings,
		isLicenceActive,
	} = useSettingsContext<AllSettings>();
	const formMethods = useForm({
		defaultValues: settings,
	});
	const { formState } = formMethods;

	usePageTitle(__('Settings', 'kudos-donations'));

	const PaymentTab = usePaymentTab();
	const tabs = applyFilters('kudosSettingsTabs', [
		PaymentTab,
		EmailTab,
		ReceiptTab,
		PlusTab,
		HelpTab,
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
								{isLicenceActive &&
									__('licence active', 'kudos-donations')}
							</span>
						</FlexItem>
						<FlexItem>
							<span
								className={clsx(
									isLicenceActive
										? 'ready status-icon'
										: 'not-ready'
								)}
							></span>
						</FlexItem>
					</Fill>
					<form
						id="settings-form"
						onSubmit={formMethods.handleSubmit(save)}
					>
						<AdminTabPanel tabs={tabs} />
						<StickySaveBar
							formId="settings-form"
							isSaving={settingsSaving}
						/>
					</form>
				</FormProvider>
			)}
		</div>
	);
};
