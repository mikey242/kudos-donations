/* eslint-disable camelcase */
import { useFormContext } from 'react-hook-form';
import BaseTab from './BaseTab';
import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useMemo } from '@wordpress/element';
import countryList from 'react-select-country-list';
import { SelectControl, TextControl } from '../controls';
import type { Campaign } from '../../../types/posts';

interface AddressTabProps {
	campaign: Campaign;
}

export const AddressTab = ({ campaign }: AddressTabProps) => {
	const {
		meta: { address_title, address_description, address_required },
	} = campaign;
	const countryOptions = useMemo(() => countryList().getData(), []);
	const { setFocus } = useFormContext();
	const optional = !address_required
		? '(' + __('optional', 'kudos-donations') + ')'
		: '';

	useEffect(() => {
		setFocus('business_name');
	}, [setFocus]);

	return (
		<BaseTab title={address_title} description={address_description}>
			<TextControl
				name="business_name"
				placeholder={sprintf(
					// translators: %s shows (optional) when field not required.
					__('Business name %s', 'kudos-donations'),
					optional
				)}
			/>
			<TextControl
				name="street"
				rules={{
					required: {
						value: address_required,
						message: __('Street required', 'kudos-donations'),
					},
				}}
				placeholder={sprintf(
					// translators: %s shows (optional) when field not required.
					__('Street %s', 'kudos-donations'),
					optional
				)}
			/>
			<TextControl
				name="postcode"
				rules={{
					required: {
						value: address_required,
						message: __('Postcode required', 'kudos-donations'),
					},
				}}
				placeholder={sprintf(
					// translators: %s shows (optional) when field not required.
					__('Postcode %s', 'kudos-donations'),
					optional
				)}
			/>
			<TextControl
				name="city"
				rules={{
					required: {
						value: address_required,
						message: __('City required', 'kudos-donations'),
					},
				}}
				placeholder={sprintf(
					// translators: %s shows (optional) when field not required.
					__('City %s', 'kudos-donations'),
					optional
				)}
			/>
			<SelectControl
				name="country"
				placeholder={sprintf(
					// translators: %s shows (optional) when field not required.
					__('Country %s', 'kudos-donations'),
					optional
				)}
				options={countryOptions}
				rules={{
					required: {
						value: address_required,
						message: __('Country required', 'kudos-donations'),
					},
				}}
			/>
		</BaseTab>
	);
};
