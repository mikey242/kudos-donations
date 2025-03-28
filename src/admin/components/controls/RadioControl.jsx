import React from 'react';
import { RadioControl as WPRadioControl } from '@wordpress/components';
import { BaseControl } from './BaseControl';

export const RadioControl = ({
	name,
	options,
	label,
	help,
	isDisabled,
	rules,
}) => {
	return (
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<WPRadioControl
					label={label}
					onChange={onChange}
					options={options}
					selected={value}
					help={description}
				/>
			)}
		/>
	);
};
