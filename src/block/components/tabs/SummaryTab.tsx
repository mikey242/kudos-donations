import { __ } from '@wordpress/i18n';
import React from 'react';
import BaseTab from './BaseTab';
import { useFormContext } from 'react-hook-form';
import { CheckboxControl } from '../controls';
import { createInterpolateElement } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import type { Campaign } from '../../../types/posts';

interface SummaryTabProps {
	campaign: Campaign;
}

interface SummaryCheckbox {
	name: string;
	enabled: string;
	label: string;
	rules?: {
		required: string;
	};
}

export const SummaryTab = ({ campaign }: SummaryTabProps) => {
	const { meta } = campaign;
	const { getValues } = useFormContext();
	const values = getValues();
	const recurringText = (): string => {
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

	// Define tabs and panels
	const checkboxes = applyFilters(
		'kudosSummaryCheckboxes',
		[
			{
				name: 'privacy',
				enabled: meta.privacy_link,
				label: createInterpolateElement(
					__('Accept <a>Privacy Policy</a>', 'kudos-donations'),
					{
						a: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							<a
								target="_blank"
								className="underline"
								href={meta.privacy_link}
								rel="noreferrer"
							></a>
						),
					}
				),
				rules: {
					required: __(
						'Please accept this to continue',
						'kudos-donations'
					),
				},
			},
			{
				name: 'terms',
				enabled: meta.terms_link,
				label: createInterpolateElement(
					__('Accept <a>Terms and Conditions</a>', 'kudos-donations'),
					{
						a: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							<a
								target="_blank"
								className="underline"
								href={meta.terms_link}
								rel="noreferrer"
							></a>
						),
					}
				),
				rules: {
					required: __(
						'Please accept this to continue',
						'kudos-donations'
					),
				},
			},
		],
		campaign
	) as SummaryCheckbox[];

	return (
		<BaseTab
			title={meta.payment_title}
			description={meta.payment_description}
		>
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
					<span>{`${window.kudos?.currencies[meta.currency]} ${values.value}`}</span>
				</p>
				<p className="my-1">
					<strong>{__('Type', 'kudos-donations')}: </strong>
					<span>{recurringText()}</span>
				</p>
			</div>

			{checkboxes.map((item, key) => {
				if (item.enabled) {
					return (
						<CheckboxControl
							key={key}
							name={item.name}
							label={item.label}
							rules={item?.rules ?? null}
						/>
					);
				}
				return '';
			})}
		</BaseTab>
	);
};

function getFrequencyName(frequency: string) {
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
