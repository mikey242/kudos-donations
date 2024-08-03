import { Fragment } from '@wordpress/element';
import { Panel } from '../Panel';
import { __ } from '@wordpress/i18n';
import { RadioGroupControl, TextControl } from '../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';

export const DonationSettingsTab = ({ recurringAllowed }) => {
	const watchAmountType = useWatch({ name: 'meta.amount_type' });

	return (
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
					label={__('Minimum allowed donation', 'kudos-donations')}
				/>
			</Panel>
		</Fragment>
	);
};
