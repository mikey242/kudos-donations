import { __ } from '@wordpress/i18n';
import React from 'react';
import BaseTab from './BaseTab';
import { useFormContext } from 'react-hook-form';
import { CheckboxControl } from '../controls';
import { createInterpolateElement } from '@wordpress/element';

export const SummaryTab = ({
	title,
	description,
	privacyLink,
	termsLink,
	currency,
}) => {
	const { getValues } = useFormContext();
	const values = getValues();

	const recurringText = () => {
		const recurring = getValues('recurring');
		if (!recurring) {
			return __('One off', 'kudos-donations');
		}
		const recurringFrequency = getFrequencyName(
			getValues('recurring_frequency')
		);
		const recurringLength = getValues('recurring_length');
		const length =
			recurringLength > 0
				? recurringLength + ' ' + __('years', 'kudos-donations')
				: __('Continuous', 'kudos-donations');
		return `${__(
			'Recurring',
			'kudos-donations'
		)} (${recurringFrequency} / ${length})`;
	};

	return (
		<BaseTab title={title} description={description}>
			<div className="kudos_summary text-left block bg-gray-100 p-2 border-0 border-solid border-t-2 border-primary">
				<p className="my-1">
					<strong>{__('Name', 'kudos-donations')}: </strong>
					<span>
						{values.email !== ''
							? values.name
							: __('anonymous', 'kudos-donations')}
					</span>
				</p>
				<p className="my-1">
					<strong>{__('E-mail address', 'kudos-donations')}: </strong>
					<span>
						{values.email !== ''
							? values.email
							: __('anonymous', 'kudos-donations')}
					</span>
				</p>
				<p className="my-1">
					<strong>{__('Amount', 'kudos-donations')}: </strong>
					<span>{`${window.kudos?.currencies[currency]} ${values.value}`}</span>
				</p>
				<p className="my-1">
					<strong>{__('Type', 'kudos-donations')}: </strong>
					<span>{recurringText()}</span>
				</p>
			</div>
			{privacyLink && (
				<CheckboxControl
					name="privacy"
					label={createInterpolateElement(
						__('Accept <a>Privacy Policy</a>', 'kudos-donations'),
						{
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a
									target="_blank"
									className="underline"
									href={privacyLink}
									rel="noreferrer"
								></a>
							),
						}
					)}
					rules={{
						required: __(
							'Please accept this to continue',
							'kudos-donations'
						),
					}}
				/>
			)}
			{termsLink && (
				<CheckboxControl
					name="terms"
					label={createInterpolateElement(
						__(
							'Accept <a>Terms and Conditions</a>',
							'kudos-donations'
						),
						{
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a
									target="_blank"
									className="underline"
									href={termsLink}
									rel="noreferrer"
								></a>
							),
						}
					)}
					rules={{
						required: __(
							'Please accept this to continue',
							'kudos-donations'
						),
					}}
				/>
			)}
		</BaseTab>
	);
};

function getFrequencyName(frequency) {
	switch (frequency) {
		case '12 months':
			return __('Yearly', 'kudos-donations');
		case '1 month':
			return __('Monthly', 'kudos-donations');
		case '3 months':
			return __('Quarterly', 'kudos-donations');
		case 'oneoff':
			return __('One-off', 'kudos-donations');
		default:
			return frequency;
	}
}
