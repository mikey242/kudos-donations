import React from 'react';
import { BaseControl, ColorPalette } from '@wordpress/components';
import { Controller } from 'react-hook-form';
import _ from 'lodash';

export const ColorPicker = ({ name, label, help, rules, isDisabled }) => {
	const colors = [
		{ name: 'Orange', color: '#ff9f1c' },
		{ name: 'Pink', color: '#ec4899' },
		{ name: 'Purple', color: '#a855f7' },
		{ name: 'Blue', color: '#3b82f6' },
		{ name: 'Green', color: '#2ec4b6' },
	];

	const id = _.uniqueId('kudos');

	return (
		<Controller
			name={name}
			rules={isDisabled ? {} : rules}
			disabled={isDisabled}
			render={({ field: { onChange, value } }) => (
				<BaseControl id={id} label={label} help={help}>
					<ColorPalette
						colors={colors}
						onChange={onChange}
						value={value}
					/>
				</BaseControl>
			)}
		/>
	);
};
