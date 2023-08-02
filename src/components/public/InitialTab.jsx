import { __, _n, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import {
	RadioGroupControl,
	TextControl,
	ToggleControl,
} from '../controls';
import { ProgressBar } from './ProgressBar';

const InitialTab = (props) => {
	const {
		title,
		description,
		buttons,
		minimumDonation = 1,
		donationType,
		amountType,
		fixedAmounts,
		goal,
		showGoal,
		total,
	} = props;

	const { setValue } = useFormContext();

	const watchFixed = useWatch({ name: 'valueFixed' });
	const watchOpen = useWatch({ name: 'valueOpen' });
	const watchValue = useWatch({ name: 'value' });

	const valueError = sprintf(
		_n(
			'Minimum donation is %d euro',
			'Minimum donation is %d euros',
			minimumDonation,
			'kudos-donations'
		),
		minimumDonation
	);

	useEffect(() => {
		if (donationType !== 'both') {
			setValue('recurring', donationType === 'recurring');
		}
	}, []);

	useEffect(() => {
		if (watchFixed) {
			setValue('value', watchFixed, { shouldValidate: true });
			setValue('valueOpen', '');
		}
	}, [watchFixed]);

	useEffect(() => {
		if (watchOpen) {
			setValue('value', watchOpen, { shouldValidate: true });
			setValue('valueFixed', '');
		}
	}, [watchOpen]);

	return (
		<BaseTab title={title} description={description} buttons={buttons}>
			{showGoal && goal > 0 && (
				<ProgressBar goal={goal} total={total} extra={watchValue} />
			)}
			{(amountType === 'both' || amountType === 'fixed') && (
				<RadioGroupControl
					name="valueFixed"
					options={fixedAmounts.map((value) => {
						return { value, label: '€' + value };
					})}
				/>
			)}

			{(amountType === 'both' || amountType === 'open') && (
				<TextControl
					name="valueOpen"
					addOn="€"
					type="number"
					placeholder={`${
						amountType === 'both'
							? __('Other amount', 'kudos-donations')
							: __('Amount', 'kudos-donations')
					}`}
				/>
			)}

			<TextControl
				type="hidden"
				name="value"
				validation={{
					required: valueError,
					min: {
						value: minimumDonation,
						message: valueError,
					},
					max: {
						value: 5000,
						message: __(
							__('Maximum donation is 5000 euros', 'kudos-donations'),
							'kudos-donations'
						),
					},
				}}
			/>

			<TextControl
				name="name"
				validation={{
					required: __('Your name is required', 'kudos-donations'),
				}}
				placeholder={__('Name', 'kudos-donations')}
			/>

			<TextControl
				name="email"
				type="email"
				validation={{
					required: __('Your email is required', 'kudos-donations'),
				}}
				placeholder={__('Email', 'kudos-donations')}
			/>

			{donationType === 'both' && (
				<div className="flex justify-center mt-3">
					<ToggleControl
						name="recurring"
						validation={{ required: true }}
						label={__('Recurring donation', 'kudos-donations')}
					/>
				</div>
			)}
		</BaseTab>
	);
};

export default InitialTab;