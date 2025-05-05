/* eslint-disable camelcase */
import { useFormContext } from 'react-hook-form';
import BaseTab from './BaseTab';
import React from 'react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from '@wordpress/element';
import countryList from 'react-select-country-list';
import { SelectControl, TextControl } from '../controls';

export const AddressTab = ({ campaign }) => {
	const {
		meta: { address_title, address_description, address_required },
	} = campaign;
	const countryOptions = useMemo(() => countryList().getData(), []);
	const { setFocus } = useFormContext();

	useEffect(() => {
		setFocus('business_name');
	}, [setFocus]);

	return (
		<BaseTab title={address_title} description={address_description}>
			<TextControl
				name="business_name"
				placeholder={__('Business name', 'kudos-donations')}
			/>
			<TextControl
				name="street"
				rules={{
					required: {
						value: address_required,
						message: __('Street required', 'kudos-donations'),
					},
				}}
				placeholder={__('Street', 'kudos-donations')}
			/>
			<TextControl
				name="postcode"
				rules={{
					required: {
						value: address_required,
						message: __('Postcode required', 'kudos-donations'),
					},
				}}
				placeholder={__('Postcode', 'kudos-donations')}
			/>
			<TextControl
				name="city"
				rules={{
					required: {
						value: address_required,
						message: __('City required', 'kudos-donations'),
					},
				}}
				placeholder={__('City', 'kudos-donations')}
			/>
			<SelectControl
				name="country"
				placeholder={__('Country', 'kudos-donations')}
				options={countryOptions}
				rules={{
					required: {
						value: address_required,
						message: __('Country required', 'kudos-donations'),
					},
				}}
				error={__('Country required', 'kudos-donations')}
			/>
		</BaseTab>
	);
};
