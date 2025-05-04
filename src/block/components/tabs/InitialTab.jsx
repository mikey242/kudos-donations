import { __, _n, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect, useMemo } from '@wordpress/element';
import { RadioGroupControl, TextControl, ToggleControl } from '../controls';
import { ProgressBar } from '../ProgressBar';

export const InitialTab = ({
	title,
	description,
	buttons,
	minimumDonation = 1,
	maximumDonation,
	donationType,
	amountType,
	fixedAmounts,
	goal,
	showGoal,
	total,
	anonymous,
	currency,
}) => {
	const currencySymbol = window.kudos?.currencies[currency];
	const { setValue } = useFormContext();
	const watchFixed = useWatch({ name: 'valueFixed' });
	const watchOpen = useWatch({ name: 'valueOpen' });
	const watchValue = useWatch({ name: 'value' });
	const watchEmail = useWatch({ name: 'email' });
	const valueError = sprintf(
		/* translators: %d is the amount in euros. */
		_n(
			'Minimum donation is %d euro',
			'Minimum donation is %d euros',
			minimumDonation,
			'kudos-donations'
		),
		minimumDonation
	);

	const isRecurringAllowed = useMemo(() => {
		return donationType === 'both' && !!watchEmail;
	}, [donationType, watchEmail]);

	const fixedAmountOptions = useMemo(() => {
		return fixedAmounts?.map((value) => ({
			value,
			label: `${currencySymbol ?? ''}${value.trim()}`,
		}));
	}, [fixedAmounts, currencySymbol]);

	useEffect(() => {
		if (!isRecurringAllowed) {
			setValue('recurring', false);
		}
	}, [isRecurringAllowed, setValue]);

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
		if (donationType !== 'both') {
			setValue('recurring', donationType === 'recurring');
		} else if (!watchEmail) {
			setValue('recurring', false);
		}
	}, [donationType, setValue, watchEmail]);

	return (
		<BaseTab title={title} description={description} buttons={buttons}>
			{showGoal && goal > 0 && (
				<div className="my-5">
					<ProgressBar
						goal={goal}
						total={total}
						extra={watchValue}
						currency={currencySymbol}
					/>
				</div>
			)}

			{amountType !== 'open' && fixedAmountOptions.length > 0 && (
				<RadioGroupControl
					name="valueFixed"
					ariaLabel={__('Fixed donation amount', 'kudos-donations')}
					options={fixedAmountOptions}
				/>
			)}

			{amountType !== 'fixed' && (
				<TextControl
					name="valueOpen"
					ariaLabel={__('Open donation amount', 'kudos-donations')}
					prefix={currencySymbol}
					type="number"
					placeholder={
						amountType === 'both'
							? __('Other amount', 'kudos-donations')
							: __('Amount', 'kudos-donations')
					}
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
						value: maximumDonation,
						message: sprintf(
							/* translators: %1$s is the currency and %2$s is the maximum donation value */
							__(
								'Maximum donation is %1$s%2$s',
								'kudos-donations'
							),
							currencySymbol,
							maximumDonation
						),
					},
				}}
			/>

			<TextControl
				name="name"
				rules={
					(!anonymous || 'recurring' === donationType) && {
						required: __(
							'Your name is required',
							'kudos-donations'
						),
					}
				}
				placeholder={
					anonymous
						? __('Full name', 'kudos-donations') +
							' (' +
							__('optional', 'kudos-donations') +
							')'
						: __('Full name', 'kudos-donations')
				}
			/>

			<TextControl
				name="email"
				type="email"
				rules={
					(!anonymous || 'recurring' === donationType) && {
						required: __(
							'Your email is required',
							'kudos-donations'
						),
					}
				}
				placeholder={
					anonymous
						? __('Email', 'kudos-donations') +
							' (' +
							__('optional', 'kudos-donations') +
							')'
						: __('Email', 'kudos-donations')
				}
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
