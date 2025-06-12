import React from 'react';
import { CheckboxControl as WPCheckboxControl } from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

export const CheckboxControl = ({
	name,
	label,
	help,
	isDisabled,
	rules,
}: ControlProps): React.ReactNode => {
	return (
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<WPCheckboxControl
					label={label}
					onChange={onChange}
					checked={value ?? false}
					help={description}
					disabled={isDisabled}
					style={{ opacity: isDisabled ? '0.3' : '1' }}
					__nextHasNoMarginBottom
				/>
			)}
		/>
	);
};
