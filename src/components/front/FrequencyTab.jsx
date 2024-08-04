import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import React from 'react';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { SelectControl } from '../common/controls';

function FrequencyTab(props) {
	const { title, description, buttons } = props;

	const { setFocus, getValues } = useFormContext();

	useEffect(() => {
		setFocus('recurring_frequency');
	}, [setFocus]);

	const isMoreThanOne = (years) => {
		const frequency = getValues('recurring_frequency');
		if (frequency) {
			return (12 / parseInt(frequency, 10)) * years !== 1;
		}
		return true;
	};

	return (
		<BaseTab title={title} description={description} buttons={buttons}>
			<SelectControl
				name="recurring_frequency"
				validation={{
					required: __(
						'Please select a payment frequency',
						'kudos-donations'
					),
				}}
				placeholder={__('Payment frequency', 'kudos-donations')}
				options={[
					{
						value: '12 months',
						label: __('Yearly', 'kudos-donations'),
					},
					{
						value: '3 months',
						label: __('Quarterly', 'kudos-donations'),
					},
					{
						value: '1 month',
						label: __('Monthly', 'kudos-donations'),
					},
				]}
			/>

			<SelectControl
				name="recurring_length"
				validation={{
					required: __(
						'Please select a payment duration',
						'kudos-donations'
					),
					validate: {
						isMoreThanOne: (v) =>
							isMoreThanOne(v) ||
							__(
								'Subscriptions must be more than one payment',
								'kudos-donations'
							),
					},
				}}
				placeholder={__('Donation duration', 'kudos-donations')}
				options={[
					{ value: '0', label: __('Continuous', 'kudos-donations') },
					{ value: '1', label: __('1 year', 'kudos-donations') },
					{ value: '2', label: __('2 years', 'kudos-donations') },
					{ value: '3', label: __('3 years', 'kudos-donations') },
					{ value: '4', label: __('4 years', 'kudos-donations') },
					{ value: '5', label: __('5 years', 'kudos-donations') },
					{ value: '6', label: __('6 years', 'kudos-donations') },
					{ value: '7', label: __('7 years', 'kudos-donations') },
					{ value: '8', label: __('8 years', 'kudos-donations') },
					{ value: '9', label: __('9 years', 'kudos-donations') },
					{ value: '10', label: __('10 years', 'kudos-donations') },
				]}
			/>
		</BaseTab>
	);
}

export default FrequencyTab;
