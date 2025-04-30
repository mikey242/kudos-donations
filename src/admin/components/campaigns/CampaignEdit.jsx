import React from 'react';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormProvider, useForm } from 'react-hook-form';
import { AdminTabPanel } from '../AdminTabPanel';
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
import { useAdminContext, useCampaignsContext } from '../contexts';
import { applyFilters } from '@wordpress/hooks';

const CampaignEdit = ({ campaign, recurringAllowed }) => {
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title?.raw,
		},
		reValidateMode: 'onSubmit',
	});
	const { reset, handleSubmit, formState } = methods;
	const { createWarningNotice } = useDispatch(noticesStore);
	const { handleUpdate } = useCampaignsContext();
	const { setPageTitle } = useAdminContext();

	useEffect(() => {
		setPageTitle(
			__('Campaign', 'kudos-donations') + ': ' + campaign.title.raw
		);
	}, [campaign, setPageTitle]);

	useEffect(() => {
		if (campaign) {
			reset({
				...campaign,
				title: campaign?.title?.raw || '',
			});
		}
	}, [campaign, reset]);

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

	const onSubmit = (data) => {
		handleUpdate(data);
	};

	const tabs = applyFilters('kudosCampaignTabs', [
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
				<DonationSettingsTab recurringAllowed={recurringAllowed} />
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
	]);

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
