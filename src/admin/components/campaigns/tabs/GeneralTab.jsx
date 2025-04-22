import { __ } from '@wordpress/i18n';
import {
	ColorPicker,
	SelectControl,
	TextAreaControl,
	TextControl,
	ToggleControl,
} from '../../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';
import { Panel, PanelBody } from '@wordpress/components';

export const GeneralTab = ({ campaign }) => {
	const { currencies } = window.kudos;
	const watchUseReturnMessage = useWatch({
		name: 'meta.show_return_message',
	});
	const watchDisplayGoal = useWatch({ name: 'meta.show_goal' });
	const watchCurrency = useWatch({ name: 'meta.currency' });
	const watchUseReturnURL = useWatch({ name: 'meta.use_custom_return_url' });

	const isValidUrl = (value) => {
		let url;

		try {
			url = new URL(value);
		} catch (_) {
			return false;
		}

		return url.protocol === 'http:' || url.protocol === 'https:';
	};

	return (
		<>
			<Panel header={__('Campaign details', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="title"
						label={__('Campaign name', 'kudos-donations')}
						help={__(
							'Give your campaign a unique name.',
							'kudos-donations'
						)}
						rules={{
							required: __('Name required', 'kudos-donations'),
						}}
					/>
					<SelectControl
						name="meta.currency"
						label={__('Currency', 'kudos-donations')}
						isDisabled={campaign.total > 0}
						prefix={currencies[watchCurrency]}
						help={__(
							'Select the desired currency for this campaign. Cannot be changed once you have received a donation.',
							'kudos-donations'
						)}
						rules={{
							required: __(
								'Please select a currency',
								'kudos-donations'
							),
						}}
						placeholder={__('Currency', 'kudos-donations')}
						options={Object.keys(currencies).map((key) => {
							return {
								label: key,
								value: key,
							};
						})}
					/>
					<TextControl
						type="number"
						name="meta.goal"
						prefix={currencies[watchCurrency]}
						help={__(
							'Set a goal for your campaign.',
							'kudos-donations'
						)}
						label={__('Goal', 'kudos-donations')}
						rules={{
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
						label={__('Display goal progress', 'kudos-donations')}
						help={__(
							'This will display a goal progress bar on your donation form. Make sure you also set a goal.',
							'kudos-donations'
						)}
					/>
					<TextControl
						type="number"
						name="meta.additional_funds"
						prefix={currencies[watchCurrency]}
						help={__(
							'Add external funds to the total.',
							'kudos-donations'
						)}
						label={__('Additional funds', 'kudos-donations')}
						rules={{
							min: {
								value: 1,
								message: __(
									'Minimum value is 1',
									'kudos-donations'
								),
							},
						}}
					/>
					<ColorPicker
						name="meta.theme_color"
						label={__('Theme color', 'kudos-donations')}
						help={__(
							'Choose a color theme for your campaign.',
							'kudos-donations'
						)}
					/>
				</PanelBody>
			</Panel>
			<Panel header={__('After payment', 'kudos-donations')}>
				<PanelBody>
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
						rules={{
							required: __(
								'Title is required',
								'kudos-donations'
							),
						}}
						label={__('Message title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.return_message_text"
						rules={{
							required: __('Message required', 'kudos-donations'),
						}}
						isDisabled={!watchUseReturnMessage}
						label={__('Message text', 'kudos-donations')}
					/>
					<ToggleControl
						name="meta.use_custom_return_url"
						label={__('Use custom return URL', 'kudos-donations')}
						help={__(
							'Once the payment has been completed, return the donor to a custom URL.',
							'kudos-donations'
						)}
					/>
					<TextControl
						name="meta.custom_return_url"
						isDisabled={!watchUseReturnURL}
						label={__('URL', 'kudos-donations')}
						rules={{
							required: __('URL required', 'kudos-donations'),
							validate: (value) => {
								return (
									isValidUrl(value) ||
									__(
										'Please enter a valid URL',
										'kudos-donations'
									)
								);
							},
						}}
					/>
				</PanelBody>
			</Panel>
		</>
	);
};
