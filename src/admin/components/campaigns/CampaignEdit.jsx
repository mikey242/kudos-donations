import React from 'react';
import { Fragment, useState } from '@wordpress/element';
import { useCopyToClipboard } from '@wordpress/compose';
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
} from '../../../common/components/controls';
import TabPanel from '../TabPanel';
import Divider from '../Divider';
import {
	ArrowCircleLeftIcon,
	ClipboardCopyIcon,
} from '@heroicons/react/outline';
import { isValidUrl } from '../../../common/helpers/util';
import KudosModal from '../../../common/components/KudosModal';

function CampaignEdit({
	root,
	campaign,
	updateCampaign,
	createNotification,
	clearCurrentCampaign,
	recurringAllowed,
}) {
	const [isModalOpen, setIsModalOpen] = useState(false);
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title?.rendered,
			'shortcode.showAs': 'button',
			'shortcode.buttonLabel': __('Donate now!', 'kudos-donations'),
		},
	});
	const { handleSubmit, watch, formState } = methods;
	const watchAmountType = watch('meta.amount_type');
	const watchUseReturnURL = watch('meta.use_custom_return_url');
	const watchAddress = watch('meta.address_enabled');
	const watchShowAs = watch('shortcode.showAs');
	const watchButtonLabel = watch('shortcode.buttonLabel');

	const toggleModal = () => setIsModalOpen((prev) => !prev);

	const onCopy = () => {
		setIsModalOpen(false);
		createNotification('Shortcode copied');
	};

	const copyRef = useCopyToClipboard(
		`[kudos campaign_id=${campaign.id} type=${watchShowAs} ${
			watchButtonLabel && watchShowAs === 'button'
				? 'button_label="' + watchButtonLabel + '"'
				: ''
		}]`,
		onCopy
	);

	const goBack = () => {
		if (Object.keys(formState.dirtyFields).length) {
			return (
				// eslint-disable-next-line no-alert
				window.confirm(
					__(
						'You have unsaved changes, are you sure you want to leave?',
						'kudos-donations'
					)
				) && clearCurrentCampaign()
			);
		}
		clearCurrentCampaign();
	};

	const onSubmit = (data) => {
		updateCampaign(data.id, data);
	};

	const tabs = [
		{
			name: 'general',
			title: __('General', 'kudos-donations'),
			content: (
				<Fragment>
					<TextControl
						name="title"
						label={__('Campaign name', 'kudos-donations')}
						help={__(
							'Give your campaign a unique name',
							'kudos-donations'
						)}
						validation={{
							required: __('Name required', 'kudos-donations'),
						}}
					/>
					<TextControl
						type="number"
						name="meta.goal"
						addOn="€"
						help={__(
							'Set a goal for your campaign',
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
						}}
					/>
					<TextControl
						type="number"
						name="meta.additional_funds"
						addOn="€"
						help={__(
							'Add external funds to the total',
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
					<ToggleControl
						name="meta.show_goal"
						label={__(
							'Display goal progress on form',
							'kudos-donations'
						)}
					/>
					<Divider />
					<ColorPicker
						name="meta.theme_color"
						label={__('Theme color', 'kudos-donations')}
						help={__(
							'Choose a color theme for your campaign',
							'kudos-donations'
						)}
					/>
					<Divider />
					<p className="block text-sm font-bold text-gray-700">
						Completed payment
					</p>
					<ToggleControl
						name="meta.show_return_message"
						label={__('Show return message', 'kudos-donations')}
					/>
					<ToggleControl
						name="meta.use_custom_return_url"
						label={__('Use custom return URL', 'kudos-donations')}
					/>
					{watchUseReturnURL && (
						<TextControl
							name="meta.custom_return_url"
							validation={{
								required: __(
									'Name required',
									'kudos-donations'
								),
								validate: (value) => isValidUrl(value),
							}}
						/>
					)}
				</Fragment>
			),
		},
		{
			name: 'text-fields',
			title: __('Text fields', 'kudos-donations'),
			content: (
				<Fragment>
					<h3>{__('Initial tab', 'kudos-donations')}</h3>
					<TextControl
						name="meta.initial_title"
						label={__('Title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.initial_description"
						label={__('Text', 'kudos-donations')}
					/>
					<Divider />
					<h3>{__('Completed payment', 'kudos-donations')}</h3>
					<TextControl
						name="meta.return_message_title"
						label={__('Message title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.return_message_text"
						label={__('Message title', 'kudos-donations')}
					/>
				</Fragment>
			),
		},
		{
			name: 'donation-settings',
			title: __('Donation settings', 'kudos-donations'),
			content: (
				<Fragment>
					<RadioGroupControl
						name="meta.donation_type"
						label={__('Donation type', 'kudos-donations')}
						help={__(
							'Choose the available payment frequency',
							'kudos-donations'
						)}
						options={[
							{
								label: __('One-off', 'kudos-donations'),
								value: 'oneoff',
							},
							{
								label: __('Subscription', 'kudos-donations'),
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
					<RadioGroupControl
						name="meta.amount_type"
						label={__('Payment type', 'kudos-donations')}
						help={__(
							'Chose the available amount types',
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
					{watchAmountType !== 'fixed' && (
						<TextControl
							name="meta.minimum_donation"
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
					)}
					{watchAmountType !== 'open' && (
						<TextControl
							name="meta.fixed_amounts"
							help={__(
								'Comma-separated list of amounts',
								'kudos-donations'
							)}
							label={__('Fixed amounts', 'kudos-donations')}
						/>
					)}
				</Fragment>
			),
		},
		{
			name: 'optional-fields',
			title: __('Optional fields', 'kudos-donations'),
			content: (
				<Fragment>
					<p className="text-sm font-bold text-gray-700">
						{__('Address', 'kudos-donations')}
					</p>
					<ToggleControl
						name="meta.address_enabled"
						label={__('Enable', 'kudos-donations')}
						help={__('Show the address tab', 'kudos-donations')}
					/>
					{watchAddress && (
						<CheckboxControl
							name="meta.address_required"
							label={__('Required', 'kudos-donations')}
						/>
					)}
					<Divider />
					<p className="text-sm font-bold text-gray-700">
						{__('Message', 'kudos-donations')}
					</p>
					<ToggleControl
						name="meta.message_enabled"
						label={__('Enable', 'kudos-donations')}
						help={__(
							'Allow donors to leave a message',
							'kudos-donations'
						)}
					/>
					<Divider />
					<TextControl
						name="meta.terms_link"
						label={__(
							'Terms and Conditions URL',
							'kudos-donations'
						)}
					/>
					<TextControl
						name="meta.privacy_link"
						label={__('Privacy Policy URL', 'kudos-donations')}
					/>
				</Fragment>
			),
		},
	];

	return (
		<Fragment>
			<h2 className="text-center my-5">
				{campaign.status === 'draft'
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
						<ArrowCircleLeftIcon className="mr-2 w-5 h-5" />
						{__('Back', 'kudos-donations')}
					</Button>
					<Button isOutline onClick={toggleModal} type="button">
						{__('Generate shortcode', 'kudos-donations')}
					</Button>
				</div>
				<KudosModal
					showLogo={false}
					isOpen={isModalOpen}
					root={root}
					toggle={toggleModal}
				>
					<>
						{/*<h1 className="text-center">*/}
						{/*    {__('Generate shortcode', 'kudos-donations')}*/}
						{/*</h1>*/}
						<RadioGroupControl
							name="shortcode.showAs"
							label={__('Display as', 'kudos-donations')}
							help={__(
								'Choose whether to show Kudos as a button or an embedded form.',
								'kudos-donations'
							)}
							options={[
								{
									label: __(
										'Button with pop-up',
										'kudos-donations'
									),
									value: 'button',
								},
								{
									label: __(
										'Embedded form',
										'kudos-donations'
									),
									value: 'form',
								},
							]}
						/>
						{watchShowAs === 'button' && (
							<>
								<Divider />
								<TextControl
									name="shortcode.buttonLabel"
									help={__(
										'Add a button label',
										'kudos-donations'
									)}
									label={__(
										'Button label',
										'kudos-donations'
									)}
								/>
							</>
						)}
						<div className="mt-8 flex justify-end relative">
							<Button
								ref={copyRef}
								onClick={() => onCopy()}
								type="button"
							>
								<ClipboardCopyIcon className="mr-2 w-5 h-5" />
								{__('Copy shortcode', 'kudos-donations')}
							</Button>
						</div>
					</>
				</KudosModal>
			</FormProvider>
		</Fragment>
	);
}

export default CampaignEdit;
