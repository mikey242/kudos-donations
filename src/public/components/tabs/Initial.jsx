import { __ } from '@wordpress/i18n';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import FormTab from './FormTab';
import { useEffect } from '@wordpress/element';
import {
	RadioGroupControl,
	TextControl,
	ToggleControl,
} from '../../../common/components/controls';
import { ProgressBar } from '../ProgressBar';

const Initial = (props) => {
	const {
		title,
		description,
		buttons,
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
		<FormTab title={title} description={description} buttons={buttons}>
			{showGoal && (
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
					required: __(
						'Minimum donation is 1 euro',
						'kudos-donations'
					),
					min: {
						value: 1,
						message: __(
							'Minimum donation is 1 euro',
							'kudos-donations'
						),
					},
					max: {
						value: 5000,
						message: __(
							'Maximum donation is 5000 euros',
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
				validation={{
					required: __('Your email is required', 'kudos-donations'),
				}}
				type="email"
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
		</FormTab>
	);
};

export default Initial;
