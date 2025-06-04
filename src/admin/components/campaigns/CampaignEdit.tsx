import React from 'react';
import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormProvider, useForm } from 'react-hook-form';
import { AdminTab, AdminTabPanel } from '../AdminTabPanel';
import {
	GeneralTab,
	TextFieldsTab,
	DonationSettingsTab,
	OptionalFieldsTab,
	CustomCSSTab,
} from './tabs';
import { store as noticesStore } from '@wordpress/notices';
import { isEmpty } from 'lodash';
import { useDispatch } from '@wordpress/data';
import {
	useAdminContext,
	useAdminQueryParams,
	usePostsContext,
} from '../contexts';
import { applyFilters } from '@wordpress/hooks';
import apiFetch from '@wordpress/api-fetch';
import type { Campaign } from '../../../types/posts';
import { Button } from '@wordpress/components';
import GenerateShortcode from './GenerateShortcode';

const NavigationButtons = ({ campaign, onBack }): React.ReactNode => (
	<>
		<Button
			variant="secondary"
			icon="arrow-left"
			onClick={onBack}
			type="button"
		>
			{__('Back', 'kudos-donations')}
		</Button>
		<GenerateShortcode campaign={campaign} />
		<Button variant="primary" type="submit" form="campaign-form">
			{__('Save', 'kudos-donations')}
		</Button>
	</>
);

interface CampaignEditProps {
	campaign: Campaign;
}

const CampaignEdit = ({ campaign }: CampaignEditProps): React.ReactNode => {
	const [, setParams] = useAdminQueryParams();
	const { setHeaderContent, setPageTitle } = useAdminContext();
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title?.raw,
		},
		reValidateMode: 'onSubmit',
	});
	const { reset, handleSubmit, formState } = methods;
	const { createWarningNotice } = useDispatch(noticesStore);
	const { handleUpdate } = usePostsContext();
	const [recurringEnabled, setRecurringEnabled] = useState<boolean>(false);

	const clearParams = useCallback(() => {
		return setParams({
			post: null,
			order: null,
			tab: null,
			paged: 1,
		});
	}, [setParams]);

	useEffect(() => {
		if (campaign) {
			setHeaderContent(
				<NavigationButtons
					campaign={campaign}
					onBack={() => {
						void clearParams();
					}}
				/>
			);
		}
		return () => {
			setHeaderContent(null);
		};
	}, [campaign, clearParams, setHeaderContent]);

	useEffect(() => {
		if (campaign) {
			setPageTitle(
				__('Campaign', 'kudos-donations') + ': ' + campaign.title.raw
			);
		}
	}, [campaign, setPageTitle]);

	useEffect(() => {
		if (campaign) {
			reset({
				...campaign,
				title: campaign.title.raw,
			});
		}
	}, [campaign, reset]);

	useEffect(() => {
		apiFetch({
			path: '/kudos/v1/payment/recurring-enabled',
			method: 'GET',
		}).then((r: boolean) => setRecurringEnabled(r));
	}, []);

	useEffect(() => {
		if (formState.isSubmitted && !isEmpty(formState.errors)) {
			void createWarningNotice(
				__(
					'There are invalid fields in the form. Please check the fields and correct any errors.',
					'kudos-donations'
				)
			);
		}
	}, [createWarningNotice, formState]);

	const onSubmit = (data: any): void => {
		void handleUpdate(data);
	};

	const tabs = useMemo(
		() =>
			applyFilters('kudosCampaignTabs', [
				{
					name: 'general',
					title: __('General', 'kudos-donations'),
					content: <GeneralTab campaign={campaign} />,
				},
				{
					name: 'text-fields',
					title: __('Text', 'kudos-donations'),
					content: <TextFieldsTab />,
				},
				{
					name: 'donation-settings',
					title: __('Donation settings', 'kudos-donations'),
					content: (
						<DonationSettingsTab
							recurringEnabled={recurringEnabled}
						/>
					),
				},
				{
					name: 'optional-fields',
					title: __('Optional fields', 'kudos-donations'),
					content: <OptionalFieldsTab />,
				},
				{
					name: 'Custom CSS',
					title: __('Custom CSS', 'kudos-donations'),
					content: <CustomCSSTab />,
				},
			]),
		[campaign, recurringEnabled]
	) as AdminTab[];

	if (!campaign) {
		return null;
	}

	return (
		<>
			<FormProvider {...methods}>
				<form id="campaign-form" onSubmit={handleSubmit(onSubmit)}>
					<AdminTabPanel tabs={tabs} />
				</form>
			</FormProvider>
		</>
	);
};

export default CampaignEdit;
