import { __, _n, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { RadioGroupControl, TextControl, ToggleControl } from '../controls';
import { ProgressBar } from '../ProgressBar';

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
		anonymous,
	} = props;

	const { setValue } = useFormContext();

	const watchFixed = useWatch({ name: 'valueFixed' });
	const watchOpen = useWatch({ name: 'valueOpen' });
	const watchValue = useWatch({ name: 'value' });
	const watchEmail = useWatch({ name: 'email' });

	const valueError = sprintf(
		// translators: %d is the amount in euros.
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
	}, [donationType, setValue]);

	useEffect(() => {
		if (watchFixed) {
			setValue('value', watchFixed, { shouldValidate: true });
			setValue('valueOpen', '');
		}
	}, [setValue, watchFixed]);

	useEffect(() => {
		if (watchOpen) {
			setValue('value', watchOpen, { shouldValidate: true });
			setValue('valueFixed', '');
		}
	}, [setValue, watchOpen]);

	useEffect(() => {
		if (!watchEmail) {
			setValue('recurring', false);
		}
	}, [setValue, watchEmail]);

	return (
		<BaseTab title={title} description={description} buttons={buttons}>
			{showGoal && goal > 0 && (
				<div className="my-5">
					<ProgressBar goal={goal} total={total} extra={watchValue} />
				</div>
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
							'Maximum donation is 5000 euros',
							'kudos-donations'
						),
					},
				}}
			/>

			<TextControl
				name="name"
				validation={
					!anonymous && {
						required: __(
							'Your name is required',
							'kudos-donations'
						),
					}
				}
				placeholder={__('Full name', 'kudos-donations')}
			/>

			<TextControl
				name="email"
				type="email"
				validation={
					!anonymous && {
						required: __(
							'Your email is required',
							'kudos-donations'
						),
					}
				}
				placeholder={__('Email', 'kudos-donations')}
			/>

			{donationType === 'both' && (
				<div className="flex justify-center mt-3">
					<ToggleControl
						isDisabled={!watchEmail}
						name="recurring"
						label={__('Recurring donation', 'kudos-donations')}
					/>
				</div>
			)}
		</BaseTab>
	);
};

export default InitialTab;
