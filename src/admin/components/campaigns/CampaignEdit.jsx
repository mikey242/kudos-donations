import React from 'react';
import { Fragment, useEffect } from '@wordpress/element';
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

import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
} from '@wordpress/components';
import { useAdminContext } from '../../contexts/AdminContext';
import { isEmpty } from 'lodash';
import { useDispatch } from '@wordpress/data';
import { useCampaignsContext } from '../../contexts/CampaignsContext';
import GenerateShortcode from './GenerateShortcode';

const CampaignEdit = ({ campaign, recurringAllowed, handleGoBack }) => {
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title?.rendered,
		},
		reValidateMode: 'onSubmit',
	});
	const { reset, handleSubmit, formState } = methods;
	const { setHeaderContent } = useAdminContext();
	const { createWarningNotice } = useDispatch(noticesStore);
	const { handleUpdate } = useCampaignsContext();

	useEffect(() => {
		if (campaign) {
			reset({
				...campaign,
				title: campaign?.title?.rendered || '',
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

	const goBack = () => {
		if (Object.keys(methods.formState.dirtyFields).length) {
			return (
				// eslint-disable-next-line no-alert
				window.confirm(
					__(
						'You have unsaved changes, are you sure you want to leave?',
						'kudos-donations'
					)
				) && handleGoBack()
			);
		}
		handleGoBack();
	};

	const onSubmit = (data) => {
		handleUpdate(data);
	};

	const NavigationButtons = () => (
		<>
			<Button
				variant="secondary"
				icon="arrow-left"
				onClick={() => goBack()}
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

	useEffect(() => {
		setHeaderContent(<NavigationButtons />);

		// Clean up on component unmount
		return () => setHeaderContent(null);
	}, [setHeaderContent]);

	const tabs = [
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
	];

	return (
		<Fragment>
			<Flex justify="center">
				<h1 className="text-center my-5">
					{__('Campaign', 'kudos-donations') +
						': ' +
						campaign.title.rendered}
				</h1>
			</Flex>
			<FormProvider {...methods}>
				<form id="campaign-form" onSubmit={handleSubmit(onSubmit)}>
					<AdminTabPanel tabs={tabs} />
				</form>
			</FormProvider>
			<Spacer marginTop={'5'} />
			<Flex justify="flex-start">
				<NavigationButtons />
			</Flex>
		</Fragment>
	);
};

export default CampaignEdit;
