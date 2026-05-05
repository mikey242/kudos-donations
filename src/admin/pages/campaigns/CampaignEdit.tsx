import { useEffect, useMemo } from '@wordpress/element';
import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { FormProvider, useForm } from 'react-hook-form';
import type { AdminTab } from '../AdminTabPanel';
import { AdminTabPanel } from '../AdminTabPanel';
import {
	CampaignDetailsPanel,
	AfterPaymentPanel,
	InitialTabPanel,
	SubscriptionTabPanel,
	AddressTabPanel,
	MessageTabPanel,
	PaymentTabPanel,
	SubscriptionPanel,
	PaymentPanel,
	OptionalFieldsPanel,
	PolicyLinksPanel,
	CustomCSSPanel,
} from './tabs';
import { store as noticesStore } from '@wordpress/notices';
import { isEmpty } from 'lodash';
import { useDispatch } from '@wordpress/data';
import { useEntitiesContext, useSettingsContext } from '../../contexts';
import { useAdminQueryParams } from '../../hooks';
import { applyFilters } from '@wordpress/hooks';
import type { Campaign } from '../../../types/entity';
import { Button, Fill } from '@wordpress/components';
import { SLOT_HEADER_ACTIONS } from '../../slot-names';
import GenerateShortcode from './GenerateShortcode';

const NavigationButtons = ({ campaign, onBack }): ReactNode => (
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

const CampaignEdit = ({ campaign }: CampaignEditProps): ReactNode => {
	const { updateParams } = useAdminQueryParams();
	const { settings } = useSettingsContext();
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title,
		},
		reValidateMode: 'onSubmit',
	});
	const { reset, handleSubmit, formState } = methods;
	const { createWarningNotice } = useDispatch(noticesStore);
	const { handleUpdate } = useEntitiesContext();

	useEffect(() => {
		if (campaign) {
			reset({
				...campaign,
				title: campaign.title,
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

	const onSubmit = (data: any): void => {
		void handleUpdate(data);
	};

	const tabs = useMemo(
		() =>
			applyFilters('kudosCampaignTabs', [
				{
					name: 'general',
					title: __('General', 'kudos-donations'),
					panels: [
						{
							name: 'campaign-details',
							content: (
								<CampaignDetailsPanel campaign={campaign} />
							),
						},
						{
							name: 'after-payment',
							content: <AfterPaymentPanel />,
						},
					],
				},
				{
					name: 'text-fields',
					title: __('Text', 'kudos-donations'),
					panels: [
						{ name: 'initial-tab', content: <InitialTabPanel /> },
						{
							name: 'subscription-tab',
							content: <SubscriptionTabPanel />,
						},
						{ name: 'address-tab', content: <AddressTabPanel /> },
						{ name: 'message-tab', content: <MessageTabPanel /> },
						{ name: 'payment-tab', content: <PaymentTabPanel /> },
					],
				},
				{
					name: 'donation-settings',
					title: __('Donation settings', 'kudos-donations'),
					panels: [
						{
							name: 'subscription',
							content: (
								<SubscriptionPanel
									recurringEnabled={
										settings._kudos_payment_vendor_status
											.recurring
									}
								/>
							),
						},
						{ name: 'payment', content: <PaymentPanel /> },
					],
				},
				{
					name: 'optional-fields',
					title: __('Optional fields', 'kudos-donations'),
					panels: [
						{
							name: 'optional-fields',
							content: <OptionalFieldsPanel />,
						},
						{ name: 'policy-links', content: <PolicyLinksPanel /> },
					],
				},
				{
					name: 'custom-css',
					title: __('Custom CSS', 'kudos-donations'),
					panels: [
						{ name: 'custom-css', content: <CustomCSSPanel /> },
					],
				},
			]),
		[campaign, settings._kudos_payment_vendor_status.recurring]
	) as AdminTab[];

	if (!campaign) {
		return null;
	}

	return (
		<>
			<Fill name={SLOT_HEADER_ACTIONS}>
				<NavigationButtons
					campaign={campaign}
					onBack={() => {
						void updateParams({ entity: null, tab: null });
					}}
				/>
			</Fill>
			<FormProvider {...methods}>
				<form id="campaign-form" onSubmit={handleSubmit(onSubmit)}>
					<AdminTabPanel tabs={tabs} />
				</form>
			</FormProvider>
		</>
	);
};

export default CampaignEdit;
