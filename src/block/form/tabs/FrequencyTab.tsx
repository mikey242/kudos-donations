/* eslint-disable camelcase */
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import React from 'react';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { SelectControl } from '../../controls';
import type { Campaign } from '../../../types/entity';
import { applyFilters } from '@wordpress/hooks';

interface FrequencyTabProps {
	campaign: Campaign;
}

export const FrequencyTab = ({ campaign }: FrequencyTabProps) => {
	const {
		subscription_title,
		subscription_description,
		frequency_options,
		duration_options,
	} = campaign;

	const { setFocus, getValues } = useFormContext();

	const filteredDuration = applyFilters(
		'kudosFormDuration',
		duration_options ?? {}
	) as Record<string, string>;

	const filteredFrequencyOptions = applyFilters(
		'kudosFormFrequencyOptions',
		frequency_options ?? {}
	) as Record<string, string>;

	useEffect(() => {
		setFocus('recurring_frequency');
	}, [setFocus]);

	const isMoreThanOne = (years: string) => {
		const frequency = getValues('recurring_frequency');
		if (frequency) {
			return (12 / parseInt(frequency, 10)) * parseInt(years, 10) !== 1;
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
				options={Object.entries(filteredFrequencyOptions).map(
					([value, label]) => ({ value, label })
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
				placeholder={__('Duration', 'kudos-donations')}
				options={Object.entries(filteredDuration).map(
					([value, label]) => ({ value, label })
				)}
			/>
		</BaseTab>
	);
};
