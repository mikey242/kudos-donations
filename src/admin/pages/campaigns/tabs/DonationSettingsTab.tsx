import { __ } from '@wordpress/i18n';
import {
	FormTokenField,
	RadioGroupControl,
	TextControl,
} from '../../../controls';
import { useFormContext, useWatch } from 'react-hook-form';
import { Panel } from '../../../components';
import { useEffect } from '@wordpress/element';
import { getCurrencySymbol } from '../../../../utils/currency';
import { useSettingsContext } from '../../../contexts';
import type { AllSettings } from '../../../../types/all-settings';

export const SubscriptionPanel = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const recurringEnabled = settings._kudos_payment_vendor_status.recurring;
	const { setValue, getValues } = useFormContext();
	const donationType = useWatch({ name: 'donation_type' });

	useEffect(() => {
		if (donationType !== 'oneoff') {
			if (!getValues('email_enabled')) {
				setValue('email_enabled', true, { shouldDirty: false });
			}
			if (!getValues('email_required')) {
				setValue('email_required', true, { shouldDirty: false });
			}
		}
	}, [donationType, getValues, setValue]);

	return (
		<Panel header={__('Subscription', 'kudos-donations')}>
			<RadioGroupControl
				name="donation_type"
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
						label: __('Subscription', 'kudos-donations'),
						value: 'recurring',
						disabled: !recurringEnabled,
					},
					{
						label: __('Both', 'kudos-donations'),
						value: 'both',
						disabled: !recurringEnabled,
					},
				]}
			/>
		</Panel>
	);
};

export const PaymentPanel = () => {
	const amountType = useWatch({ name: 'amount_type' });
	const currency = useWatch({ name: 'currency' });
	const maxDonation = useWatch({ name: 'maximum_donation' });
	const minDonation = useWatch({ name: 'minimum_donation' });
	const currencySymbol = getCurrencySymbol(currency);

	return (
		<Panel header={__('Payment', 'kudos-donations')}>
			<RadioGroupControl
				name="amount_type"
				label={__('Payment type', 'kudos-donations')}
				help={__(
					'Chose the available amount types.',
					'kudos-donations'
				)}
				options={[
					{ label: __('Open', 'kudos-donations'), value: 'open' },
					{ label: __('Fixed', 'kudos-donations'), value: 'fixed' },
					{
						label: __('Open + Fixed', 'kudos-donations'),
						value: 'both',
					},
				]}
			/>
			<FormTokenField
				name="fixed_amounts"
				isDisabled={amountType === 'open'}
				maxLength={5}
				rules={{
					validate: (value) => {
						if (!value || value.length === 0) {
							return __(
								'You need to enter one or more amounts',
								'kudos-donations'
							);
						}
						for (const item of value) {
							if (!/^\d*\.?\d+$/.test(item)) {
								return __(
									'All values must be numbers',
									'kudos-donations'
								);
							}
							const numberValue = parseFloat(item);
							if (numberValue < minDonation) {
								return (
									__(
										`Each value must be greater than or equal to the minimum donation amount`,
										'kudos-donations'
									) + ` (${minDonation}).`
								);
							}
							if (numberValue > maxDonation) {
								return (
									__(
										`Each value must not exceed the maximum donation amount`,
										'kudos-donations'
									) + ` (${maxDonation}).`
								);
							}
						}
						return true;
					},
					required: __(
						'You need to enter one or more amounts',
						'kudos-donations'
					),
				}}
				help={__(
					'List of fixed amounts (max 5). Enter a single value to lock the campaign to that amount.',
					'kudos-donations'
				)}
				label={__('Fixed amounts', 'kudos-donations')}
			/>
			<TextControl
				name="minimum_donation"
				type="number"
				prefix={currencySymbol}
				help={__(
					'This is the minimum donation that will be accepted.',
					'kudos-donations'
				)}
				rules={{
					required: __(
						'Minimum donation required',
						'kudos-donations'
					),
					min: {
						value: 1,
						message: __('Minimum value is 1', 'kudos-donations'),
					},
				}}
				label={__('Minimum allowed donation', 'kudos-donations')}
			/>
			<TextControl
				name="maximum_donation"
				prefix={currencySymbol}
				type="number"
				label={__('Maximum donation', 'kudos-donations')}
				help={__(
					"The maximum donation that you want to allow, leave blank to disable. This does not override your payment provider's maximum.",
					'kudos-donations'
				)}
			/>
		</Panel>
	);
};
