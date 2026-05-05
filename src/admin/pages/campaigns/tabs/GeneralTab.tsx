import { __ } from '@wordpress/i18n';
import {
	ColorPicker,
	SelectControl,
	TextAreaControl,
	TextControl,
	ToggleControl,
} from '../../../controls';
import type { ReactNode } from 'react';
import { useWatch } from 'react-hook-form';
import type { Campaign } from '../../../../types/entity';
import { Panel } from '../../../components';
import { isValidUrl } from '../../../../utils';
import { getCurrencySymbol } from '../../../../utils/currency';
import { PanelList } from '../../AdminTabPanel';

interface GeneralTabProps {
	campaign: Campaign;
}

const CampaignDetailsPanel = ({ campaign }: GeneralTabProps) => {
	const { currencies } = window.kudos;
	const watchDisplayGoal = useWatch({ name: 'show_goal' });
	const watchCurrency = useWatch({ name: 'currency' });
	const currencySymbol = getCurrencySymbol(watchCurrency);

	return (
		<Panel header={__('Campaign details', 'kudos-donations')}>
			<TextControl
				name="title"
				label={__('Campaign name', 'kudos-donations')}
				help={__(
					'Give your campaign a unique name.',
					'kudos-donations'
				)}
				rules={{ required: __('Name required', 'kudos-donations') }}
			/>
			<SelectControl
				name="currency"
				label={__('Currency', 'kudos-donations')}
				isDisabled={campaign.total > 0}
				prefix={currencySymbol}
				help={__(
					'Select the desired currency for this campaign. Cannot be changed once you have received a donation.',
					'kudos-donations'
				)}
				rules={{
					required: __('Please select a currency', 'kudos-donations'),
				}}
				options={Object.keys(currencies).map((key) => ({
					label: key,
					value: key,
				}))}
			/>
			<TextControl
				type="number"
				name="goal"
				prefix={currencySymbol}
				help={__('Set a goal for your campaign.', 'kudos-donations')}
				label={__('Goal', 'kudos-donations')}
				rules={{
					min: {
						value: 1,
						message: __('Minimum value is 1', 'kudos-donations'),
					},
					validate: (value) =>
						watchDisplayGoal && !value
							? __('Please enter a goal', 'kudos-donations')
							: true,
				}}
			/>
			<ToggleControl
				name="show_goal"
				label={__('Display goal progress', 'kudos-donations')}
				help={__(
					'This will display a goal progress bar on your donation form. Make sure you also set a goal.',
					'kudos-donations'
				)}
			/>
			<TextControl
				type="number"
				name="additional_funds"
				prefix={currencySymbol}
				help={__('Add external funds to the total.', 'kudos-donations')}
				label={__('Additional funds', 'kudos-donations')}
				rules={{
					min: {
						value: 1,
						message: __('Minimum value is 1', 'kudos-donations'),
					},
				}}
			/>
			<ColorPicker
				name="theme_color"
				label={__('Theme color', 'kudos-donations')}
				help={__(
					'Choose a color theme for your campaign.',
					'kudos-donations'
				)}
			/>
		</Panel>
	);
};

const AfterPaymentPanel = () => {
	const watchUseReturnMessage = useWatch({ name: 'show_return_message' });
	const watchUseReturnURL = useWatch({ name: 'use_custom_return_url' });

	return (
		<Panel header={__('After payment', 'kudos-donations')}>
			<ToggleControl
				name="show_return_message"
				label={__('Show return message', 'kudos-donations')}
				help={__(
					'This will show a pop-up message to the donor thanking them for their donation.',
					'kudos-donations'
				)}
			/>
			<TextControl
				name="return_message_title"
				isDisabled={!watchUseReturnMessage}
				rules={{ required: __('Title is required', 'kudos-donations') }}
				label={__('Message title', 'kudos-donations')}
			/>
			<TextAreaControl
				name="return_message_text"
				rules={{ required: __('Message required', 'kudos-donations') }}
				isDisabled={!watchUseReturnMessage}
				label={__('Message text', 'kudos-donations')}
			/>
			<ToggleControl
				name="use_custom_return_url"
				label={__('Use custom return URL', 'kudos-donations')}
				help={__(
					'Once the payment has been completed, return the donor to a custom URL.',
					'kudos-donations'
				)}
			/>
			<TextControl
				name="custom_return_url"
				isDisabled={!watchUseReturnURL}
				label={__('URL', 'kudos-donations')}
				rules={{
					required: __('URL required', 'kudos-donations'),
					validate: (value) =>
						isValidUrl(value) ||
						__('Please enter a valid URL', 'kudos-donations'),
				}}
			/>
		</Panel>
	);
};

export const GeneralTab = ({ campaign }: GeneralTabProps): ReactNode => (
	<PanelList
		namespace="kudosCampaignPanels"
		tabName="general"
		defaultPanels={[
			{
				name: 'campaign-details',
				content: <CampaignDetailsPanel campaign={campaign} />,
			},
			{
				name: 'after-payment',
				content: <AfterPaymentPanel />,
			},
		]}
		args={campaign}
	/>
);
