import React from 'react';
import { useId } from '@wordpress/element';
import {
	BaseControl as WPBaseControl,
	ColorPalette,
} from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

export const ColorPicker = ({
	name,
	label,
	help,
	rules,
	isDisabled,
}: ControlProps): React.ReactNode => {
	const colors = [
		{ name: 'Orange', color: '#ff9f1c' },
		{ name: 'Pink', color: '#ec4899' },
		{ name: 'Purple', color: '#a855f7' },
		{ name: 'Blue', color: '#3b82f6' },
		{ name: 'Green', color: '#2ec4b6' },
	];

	const id = useId();

	return (
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<WPBaseControl
					id={id}
					label={label}
					help={description}
				>
					<ColorPalette
						colors={colors}
						onChange={onChange}
						value={value}
					/>
				</WPBaseControl>
			)}
		/>
	);
};
