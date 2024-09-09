import React from 'react';
import { RadioControl as WPRadioControl } from '@wordpress/components';
import { BaseControl } from './BaseControl';

const RadioControl = ({
	name,
	options,
	label,
	help,
	isDisabled,
	validation,
}) => {
	return (
		<BaseControl
			name={name}
			rules={validation}
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

export { RadioControl };
