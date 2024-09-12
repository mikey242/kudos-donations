import { __, _n, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { RadioGroupControl, TextControl, ToggleControl } from '../controls';
import { ProgressBar } from '../ProgressBar';

export const InitialTab = ({
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
	currency,
}) => {
	const { currencies, maxDonation } = window.kudos;
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
					<ProgressBar
						goal={goal}
						total={total}
						extra={watchValue}
						currency={currencies[currency]}
					/>
				</div>
			)}
			{(amountType === 'both' || amountType === 'fixed') &&
				fixedAmounts?.length && (
					<RadioGroupControl
						name="valueFixed"
						ariaLabel={__(
							'Fixed donation amount',
							'kudos-donations'
						)}
						options={fixedAmounts.map((value) => {
							return {
								value,
								label:
									(currencies[currency] ?? '') +
									value?.trim(),
							};
						})}
					/>
				)}

			{(amountType === 'both' || amountType === 'open') && (
				<TextControl
					name="valueOpen"
					ariaLabel={__('Open donation amount', 'kudos-donations')}
					prefix={currencies[currency]}
					type="number"
					rules={{
						max: {
							value: maxDonation,
							message: sprintf(
								// translators: %s is the maximum donation value
								__('Maximum donation is %s', 'kudos-donations'),
								maxDonation
							),
						},
					}}
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
				rules={{
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
				rules={
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
				rules={
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
