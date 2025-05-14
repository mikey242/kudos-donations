/* eslint-disable camelcase */
import { __, _n } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import React from 'react';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { SelectControl } from '../controls';
import { Campaign } from '../../contexts/CampaignContext';

interface FrequencyTabProps {
	campaign: Campaign;
}

export const FrequencyTab = ({ campaign }: FrequencyTabProps) => {
	const {
		meta: {
			subscription_title,
			subscription_description,
			frequency_options,
		},
	} = campaign;

	const { setFocus, getValues } = useFormContext();

	const duration = [];
	[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10].forEach((i) =>
		duration.push({
			value: i,
			label:
				i === 0
					? __('Continuous', 'kudos-donations')
					: i + ' ' + _n('year', 'years', i, 'kudos-donations'),
		})
	);

	useEffect(() => {
		setFocus('recurring_frequency');
	}, [setFocus]);

	const isMoreThanOne = (years: number) => {
		const frequency = getValues('recurring_frequency');
		if (frequency) {
			return (12 / parseInt(frequency, 10)) * years !== 1;
		}
		return true;
	};

	return (
		<BaseTab
			title={subscription_title}
			description={subscription_description}
		>
			<SelectControl
				name="recurring_frequency"
				rules={{
					required: __(
						'Please select a payment frequency',
						'kudos-donations'
					),
				}}
				placeholder={__('Payment frequency', 'kudos-donations')}
				options={Object.entries(frequency_options).map(
					([value, label]) => ({
						value,
						label,
					})
				)}
			/>

			<SelectControl
				name="recurring_length"
				rules={{
					required: __(
						'Please select a payment duration',
						'kudos-donations'
					),
					validate: (v) =>
						isMoreThanOne(v) ||
						__(
							'Subscriptions must be more than one payment',
							'kudos-donations'
						),
				}}
				placeholder={__('Donation duration', 'kudos-donations')}
				options={duration}
			/>
		</BaseTab>
	);
};
