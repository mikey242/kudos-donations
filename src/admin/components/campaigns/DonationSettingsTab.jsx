import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormTokenField, RadioGroupControl, TextControl } from '../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';
import { Panel, PanelBody } from '@wordpress/components';

export const DonationSettingsTab = ({ recurringAllowed }) => {
	const watchAmountType = useWatch({ name: 'meta.amount_type' });

	return (
		<Fragment>
			<Panel header={__('Subscription', 'kudos-donations')}>
				<PanelBody>
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
				</PanelBody>
			</Panel>
			<Panel header={__('Payment', 'kudos-donations')}>
				<PanelBody>
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
						isDisabled={watchAmountType === 'open'}
						maxLength={5}
						validation={{
							validate: (value) => {
								return (
									value.every((item) =>
										/^\d*\.?\d+$/.test(item)
									) ||
									__(
										'All values must be numbers',
										'kudos-donations'
									)
								);
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
						isDisabled={watchAmountType === 'fixed'}
						prefix="€"
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
				</PanelBody>
			</Panel>
		</Fragment>
	);
};
