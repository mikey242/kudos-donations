/* eslint-disable camelcase */
import { __ } from '@wordpress/i18n';
import { FormTokenField, RadioGroupControl, TextControl } from '../../controls';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { applyFilters } from '@wordpress/hooks';
import { Panel } from '../../Panel';
import { useEffect } from '@wordpress/element';

interface DonationSettingsTabProps {
	recurringEnabled?: boolean;
}
export const DonationSettingsTab = ({
	recurringEnabled = false,
}: DonationSettingsTabProps): React.ReactNode => {
	const { currencies } = window.kudos;
	const { setValue, getValues } = useFormContext();
	const amountType = useWatch({ name: 'meta.amount_type' });
	const currency = useWatch({ name: 'meta.currency' });
	const maxDonation = useWatch({ name: 'meta.maximum_donation' });
	const minDonation = useWatch({ name: 'meta.minimum_donation' });
	const donationType = useWatch({ name: 'meta.donation_type' });

	useEffect(() => {
		if (donationType !== 'oneoff') {
			// Only force email_enabled if it's currently false
			if (!getValues('meta.email_enabled')) {
				setValue('meta.email_enabled', true, { shouldDirty: false });
			}
			// Always enforce internal flag for email requirement
			if (!getValues('meta.email_required')) {
				setValue('meta.email_required', true, { shouldDirty: false });
			}
		}
	}, [donationType, getValues, setValue]);

	return (
		<>
			<Panel header={__('Subscription', 'kudos-donations')}>
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
			<Panel header={__('Payment', 'kudos-donations')}>
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
				<FormTokenField
					name="meta.fixed_amounts"
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

							return true; // Return true if all validations pass
						},
						required: __(
							'You need to enter one or more amounts',
							'kudos-donations'
						),
					}}
					help={__(
						'List of fixed amounts (max 5).',
						'kudos-donations'
					)}
					label={__('Fixed amounts', 'kudos-donations')}
				/>
				<TextControl
					name="meta.minimum_donation"
					type="number"
					prefix={currencies[currency]}
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
							message: __(
								'Minimum value is 1',
								'kudos-donations'
							),
						},
					}}
					label={__('Minimum allowed donation', 'kudos-donations')}
				/>
				<TextControl
					name="meta.maximum_donation"
					prefix={currencies[currency]}
					type="number"
					label={__('Maximum donation', 'kudos-donations')}
					help={__(
						"The maximum donation that you want to allow, leave blank to disable. This does not override your payment provider's maximum.",
						'kudos-donations'
					)}
				/>
			</Panel>
			<>{applyFilters('kudosCampaignsDonationEnd', '', useFormContext)}</>
		</>
	);
};
