/* eslint-disable camelcase */
import { __, _n, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect, useMemo } from '@wordpress/element';
import { RadioGroupControl, TextControl, ToggleControl } from '../controls';
import type { RadioGroupOption } from '../controls';
import { ProgressBar } from '../ProgressBar';
import type { Campaign } from '../../../types/wp';

interface InitialTabProps {
	campaign: Campaign;
}

export const InitialTab = ({ campaign }: InitialTabProps) => {
	const {
		meta: {
			initial_title,
			initial_description,
			currency,
			minimum_donation,
			donation_type,
			fixed_amounts,
			amount_type,
			maximum_donation,
			allow_anonymous,
			show_goal,
			goal,
		},
		total,
	} = campaign;
	const currencySymbol = useMemo(() => {
		return window.kudos?.currencies?.[currency] ?? currency;
	}, [currency]);
	const { setValue } = useFormContext();
	const watchFixed: string = useWatch({ name: 'valueFixed' });
	const watchOpen: string = useWatch({ name: 'valueOpen' });
	const watchValue: number = useWatch({ name: 'value' });
	const watchEmail: string = useWatch({ name: 'email' });
	const valueError = sprintf(
		/* translators: %d is the amount in euros. */
		_n(
			'Minimum donation is %d euro',
			'Minimum donation is %d euros',
			minimum_donation,
			'kudos-donations'
		),
		minimum_donation
	);

	const isRecurringAllowed = useMemo(() => {
		return donation_type === 'both' && !!watchEmail;
	}, [donation_type, watchEmail]);

	const fixedAmountOptions: RadioGroupOption[] = useMemo(() => {
		return fixed_amounts?.map((value) => ({
			value,
			label: `${currencySymbol ?? ''}${value.trim()}`,
		}));
	}, [fixed_amounts, currencySymbol]);

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
		if (donation_type !== 'both') {
			setValue('recurring', donation_type === 'recurring');
		} else if (!watchEmail) {
			setValue('recurring', false);
		}
	}, [donation_type, setValue, watchEmail]);

	return (
		<BaseTab title={initial_title} description={initial_description}>
			{show_goal && goal > 0 && (
				<div className="my-5">
					<ProgressBar
						goal={goal}
						total={total}
						extra={watchValue}
						currency={currencySymbol}
					/>
				</div>
			)}

			{amount_type !== 'open' && fixedAmountOptions.length > 0 && (
				<RadioGroupControl
					name="valueFixed"
					ariaLabel={__('Fixed donation amount', 'kudos-donations')}
					options={fixedAmountOptions}
				/>
			)}

			{amount_type !== 'fixed' && (
				<TextControl
					name="valueOpen"
					ariaLabel={__('Open donation amount', 'kudos-donations')}
					prefix={currencySymbol}
					type="number"
					placeholder={
						amount_type === 'both'
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
						value: minimum_donation,
						message: valueError,
					},
					max: {
						value: maximum_donation,
						message: sprintf(
							/* translators: %1$s is the currency and %2$s is the maximum donation value */
							__(
								'Maximum donation is %1$s%2$s',
								'kudos-donations'
							),
							currencySymbol,
							maximum_donation
						),
					},
				}}
			/>

			<TextControl
				name="name"
				rules={
					(!allow_anonymous || 'recurring' === donation_type) && {
						required: __(
							'Your name is required',
							'kudos-donations'
						),
					}
				}
				placeholder={
					allow_anonymous
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
					(!allow_anonymous || 'recurring' === donation_type) && {
						required: __(
							'Your email is required',
							'kudos-donations'
						),
					}
				}
				placeholder={
					allow_anonymous
						? __('Email', 'kudos-donations') +
							' (' +
							__('optional', 'kudos-donations') +
							')'
						: __('Email', 'kudos-donations')
				}
			/>

			{donation_type === 'both' && (
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
