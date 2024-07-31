import React from 'react';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormProvider, useForm } from 'react-hook-form';
import {
	Button,
	CheckboxControl,
	ColorPicker,
	RadioGroupControl,
	TextAreaControl,
	TextControl,
	ToggleControl,
} from '../controls';
import TabPanel from '../admin/TabPanel';
import { isValidUrl } from '../../helpers/util';
import GenerateShortcode from './GenerateShortcode';
import { ArrowLeftCircleIcon } from '@heroicons/react/24/outline';
import { useAdminTableContext } from '../../contexts/AdminTableContext';
import { Panel } from '../Panel';
import Divider from '../Divider';

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
	const { handleSubmit, watch, formState, reset } = methods;
	const watchAmountType = watch('meta.amount_type');
	const watchUseReturnURL = watch('meta.use_custom_return_url');
	const watchAddress = watch('meta.address_enabled');
	const watchUseReturnMessage = watch('meta.show_return_message');
	const watchDisplayGoal = watch('meta.show_goal');
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
			content: (
				<Fragment>
					<Panel title={__('Campaign details', 'kudos-donations')}>
						<TextControl
							name="title"
							label={__('Campaign name', 'kudos-donations')}
							help={__(
								'Give your campaign a unique name.',
								'kudos-donations'
							)}
							validation={{
								required: __(
									'Name required',
									'kudos-donations'
								),
							}}
						/>
						<Divider />
						<TextControl
							type="number"
							name="meta.goal"
							addOn="€"
							help={__(
								'Set a goal for your campaign.',
								'kudos-donations'
							)}
							label={__('Goal', 'kudos-donations')}
							validation={{
								min: {
									value: 1,
									message: __(
										'Minimum value is 1',
										'kudos-donations'
									),
								},
								validate: (value) =>
									watchDisplayGoal && !value
										? __(
												'Please enter a goal',
												'kudos-donations'
											)
										: true,
							}}
						/>
						<ToggleControl
							name="meta.show_goal"
							label={__(
								'Display goal progress',
								'kudos-donations'
							)}
							help={__(
								'This will display a goal progress bar on your donation form. Make sure you also set a goal.',
								'kudos-donations'
							)}
						/>
						<TextControl
							type="number"
							name="meta.additional_funds"
							addOn="€"
							help={__(
								'Add external funds to the total.',
								'kudos-donations'
							)}
							label={__('Additional funds', 'kudos-donations')}
							validation={{
								min: {
									value: 1,
									message: __(
										'Minimum value is 1',
										'kudos-donations'
									),
								},
							}}
						/>
						<Divider />
						<ColorPicker
							name="meta.theme_color"
							label={__('Theme color', 'kudos-donations')}
							help={__(
								'Choose a color theme for your campaign.',
								'kudos-donations'
							)}
						/>
					</Panel>
					<Panel title={__('After payment', 'kudos-donations')}>
						<ToggleControl
							name="meta.show_return_message"
							label={__('Show return message', 'kudos-donations')}
							help={__(
								'This will show a pop-up message to the donor thanking them for their donation.',
								'kudos-donations'
							)}
						/>
						<TextControl
							name="meta.return_message_title"
							isDisabled={!watchUseReturnMessage}
							validation={{
								required: __(
									'Title is required',
									'kudos-donations'
								),
							}}
							label={__('Message title', 'kudos-donations')}
						/>
						<TextAreaControl
							name="meta.return_message_text"
							validation={{
								required: __(
									'Message required',
									'kudos-donations'
								),
							}}
							isDisabled={!watchUseReturnMessage}
							label={__('Message text', 'kudos-donations')}
						/>
						<Divider />
						<ToggleControl
							name="meta.use_custom_return_url"
							label={__(
								'Use custom return URL',
								'kudos-donations'
							)}
							help={__(
								'Once the payment has been completed, return the donor to a custom URL.',
								'kudos-donations'
							)}
						/>
						<TextControl
							name="meta.custom_return_url"
							isDisabled={!watchUseReturnURL}
							label={__('URL', 'kudos-donations')}
							validation={{
								required: __('URL required', 'kudos-donations'),
								validate: (value) => isValidUrl(value),
							}}
						/>
					</Panel>
				</Fragment>
			),
		},
		{
			name: 'text-fields',
			title: __('Text fields', 'kudos-donations'),
			content: (
				<Fragment>
					<Panel title={__('Initial tab', 'kudos-donations')}>
						<TextControl
							name="meta.initial_title"
							label={__('Title', 'kudos-donations')}
						/>
						<TextAreaControl
							name="meta.initial_description"
							label={__('Text', 'kudos-donations')}
						/>
					</Panel>
					<Panel title={__('Subscription tab', 'kudos-donations')}>
						<TextControl
							name="meta.subscription_title"
							label={__('Title', 'kudos-donations')}
						/>
						<TextAreaControl
							name="meta.subscription_description"
							label={__('Text', 'kudos-donations')}
						/>
					</Panel>
					<Panel title={__('Address tab', 'kudos-donations')}>
						<TextControl
							name="meta.address_title"
							label={__('Title', 'kudos-donations')}
						/>
						<TextAreaControl
							name="meta.address_description"
							label={__('Text', 'kudos-donations')}
						/>
					</Panel>
					<Panel title={__('Message tab', 'kudos-donations')}>
						<TextControl
							name="meta.message_title"
							label={__('Title', 'kudos-donations')}
						/>
						<TextAreaControl
							name="meta.message_description"
							label={__('Text', 'kudos-donations')}
						/>
					</Panel>
					<Panel title={__('Payment tab', 'kudos-donations')}>
						<TextControl
							name="meta.payment_title"
							label={__('Title', 'kudos-donations')}
						/>
						<TextAreaControl
							name="meta.payment_description"
							label={__('Text', 'kudos-donations')}
						/>
					</Panel>
				</Fragment>
			),
		},
		{
			name: 'donation-settings',
			title: __('Donation settings', 'kudos-donations'),
			content: (
				<Fragment>
					<Panel title={__('Subscription', 'kudos-donations')}>
						<RadioGroupControl
							name="meta.donation_type"
							label={__('Donation type', 'kudos-donations')}
							help={__(
								'Choose the available payment frequency.',
								'kudos-donations'
							)}
							options={[
								{
									label: __('One-off', 'kudos-donations'),
									value: 'oneoff',
								},
								{
									label: __(
										'Subscription',
										'kudos-donations'
									),
									value: 'recurring',
									disabled: !recurringAllowed,
								},
								{
									label: __('Both', 'kudos-donations'),
									value: 'both',
									disabled: !recurringAllowed,
								},
							]}
						/>
					</Panel>
					<Panel title={__('Payment', 'kudos-donations')}>
						<RadioGroupControl
							name="meta.amount_type"
							label={__('Payment type', 'kudos-donations')}
							help={__(
								'Chose the available amount types.',
								'kudos-donations'
							)}
							options={[
								{
									label: __('Open', 'kudos-donations'),
									value: 'open',
								},
								{
									label: __('Fixed', 'kudos-donations'),
									value: 'fixed',
								},
								{
									label: __('Both', 'kudos-donations'),
									value: 'both',
								},
							]}
						/>
						<TextControl
							name="meta.fixed_amounts"
							isDisabled={watchAmountType === 'open'}
							validation={{
								validate: (value) => {
									const regex = /^(\d+\s*)(\s*,\s*\d+\s*)*$/;
									return regex.test(value)
										? true
										: __(
												'Value needs to be a comma separated list of numbers'
											);
								},
								required: __(
									'You need to enter one or more amounts',
									'kudos-donations'
								),
							}}
							help={__(
								'Comma-separated list of amounts.',
								'kudos-donations'
							)}
							label={__('Fixed amounts', 'kudos-donations')}
						/>
						<TextControl
							name="meta.minimum_donation"
							isDisabled={watchAmountType === 'fixed'}
							addOn="€"
							help={__(
								'This is the minimum donation that will be accepted.',
								'kudos-donations'
							)}
							validation={{
								required: __(
									'Minimum donation required',
									'kudos-donations'
								),
								min: {
									value: 1,
									message: __(
										'Minimum value is 1',
										'kudos-donations'
									),
								},
							}}
							label={__(
								'Minimum allowed donation',
								'kudos-donations'
							)}
						/>
					</Panel>
				</Fragment>
			),
		},
		{
			name: 'optional-fields',
			title: __('Optional fields', 'kudos-donations'),
			content: (
				<Fragment>
					<Panel title={__('Optional fields', 'kudos-donations')}>
						<ToggleControl
							name="meta.allow_anonymous"
							label={__(
								'Allow anonymous donations',
								'kudos-donations'
							)}
							help={__(
								'Allow users to donate without leaving a name or email address. Anonymous users can only perform one-off donations.',
								'kudos-donations'
							)}
						/>
						<Divider />
						<ToggleControl
							name="meta.address_enabled"
							label={__('Address', 'kudos-donations')}
							help={__(
								'Show the address tab.',
								'kudos-donations'
							)}
						/>
						<CheckboxControl
							name="meta.address_required"
							isDisabled={!watchAddress}
							help={__(
								'Make the address required.',
								'kudos-donations'
							)}
							label={' '}
							altLabel={__('Required', 'kudos-donations')}
						/>
						<Divider />
						<ToggleControl
							name="meta.message_enabled"
							label={__('Message', 'kudos-donations')}
							help={__(
								'Allow donors to leave a message.',
								'kudos-donations'
							)}
						/>
					</Panel>
					<Panel title={__('Policy links', 'kudos-donations')}>
						<TextControl
							name="meta.terms_link"
							label={__(
								'Terms & Conditions URL',
								'kudos-donations'
							)}
							help={__(
								'Add a URL to your Terms & Conditions, donors will need to agree to them before donating.',
								'kudos-donations'
							)}
						/>
						<TextControl
							name="meta.privacy_link"
							label={__('Privacy Policy URL', 'kudos-donations')}
							help={__(
								'Add a URL to your Privacy policy, donors will need to agree to it before donating.',
								'kudos-donations'
							)}
						/>
					</Panel>
				</Fragment>
			),
		},
		{
			name: 'Custom CSS',
			title: __('Custom CSS', 'kudos-donations'),
			content: (
				<Panel title={__('Custom CSS', 'kudos-donations')}>
					<TextAreaControl
						help="Enter your custom css here. This will only apply to the current campaign."
						label={__('Custom CSS', 'kudos-donations')}
						hideLabel={true}
						name="meta.custom_styles"
					/>
				</Panel>
			),
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
