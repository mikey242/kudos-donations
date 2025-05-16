import React from 'react';
import { RadioControl as WPRadioControl } from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

export interface RadioOption {
	label: string;
	value: string;
}

interface RadioControlProps extends ControlProps {
	options: RadioOption[];
}

export const RadioControl = ({
	name,
	options,
	label,
	help,
	isDisabled,
	rules,
}: RadioControlProps): React.ReactNode => {
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
