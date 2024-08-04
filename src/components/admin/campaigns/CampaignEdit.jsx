import React from 'react';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormProvider, useForm } from 'react-hook-form';
import { Button } from '../../common/controls';
import TabPanel from '../TabPanel';
import GenerateShortcode from './GenerateShortcode';
import { ArrowLeftCircleIcon } from '@heroicons/react/24/outline';
import { useAdminTableContext } from '../../common/contexts/AdminTableContext';
import { GeneralTab } from './GeneralTab';
import { TextFieldsTab } from './TextFieldsTab';
import { DonationSettingsTab } from './DonationSettingsTab';
import { OptionalFieldsTab } from './OptionalFieldsTab';
import { CustomCSSTab } from './CustomCSSTab';

const CampaignEdit = ({ campaign, updateCampaign, recurringAllowed }) => {
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title?.rendered,
			'shortcode.showAs': 'button',
			'shortcode.buttonLabel': __('Donate now!', 'kudos-donations'),
		},
	});
	const { clearCurrentPost } = useAdminTableContext();
	const { handleSubmit, formState, reset } = methods;
	const isNew = campaign.status === 'draft';

	const goBack = () => {
		if (Object.keys(formState.dirtyFields).length) {
			return (
				// eslint-disable-next-line no-alert
				window.confirm(
					__(
						'You have unsaved changes, are you sure you want to leave?',
						'kudos-donations'
					)
				) && clearCurrentPost()
			);
		}
		clearCurrentPost();
	};

	const onSubmit = (data) => {
		updateCampaign(data.id, data).finally(() => {
			reset(data);
		});
	};

	const tabs = [
		{
			name: 'general',
			title: __('General', 'kudos-donations'),
			content: <GeneralTab campaign={campaign} />,
		},
		{
			name: 'text-fields',
			title: __('Text fields', 'kudos-donations'),
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
			<h2 className="text-center my-5">
				{isNew
					? __('New campaign', 'kudos-donations')
					: __('Edit campaign: ', 'kudos-donations') +
						campaign.title.rendered}
			</h2>
			<FormProvider {...methods}>
				<form id="settings-form" onSubmit={handleSubmit(onSubmit)}>
					<TabPanel tabs={tabs} />
				</form>
				<div className="text-right flex justify-start mt-5 pb-2">
					<Button
						className="mr-2"
						onClick={() => goBack()}
						type="button"
					>
						<ArrowLeftCircleIcon className="mr-2 w-5 h-5" />
						{__('Back', 'kudos-donations')}
					</Button>
					{!isNew && <GenerateShortcode campaign={campaign} />}
				</div>
			</FormProvider>
		</Fragment>
	);
};

export default CampaignEdit;
