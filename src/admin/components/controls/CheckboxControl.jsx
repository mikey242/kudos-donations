import React from 'react';
import { CheckboxControl as WPCheckboxControl } from '@wordpress/components';
import { BaseControl } from './BaseControl';

export const CheckboxControl = ({ name, label, help, isDisabled, rules }) => {
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
					checked={value}
					help={description}
					disabled={isDisabled}
					__nextHasNoMarginBottom
				/>
			)}
		/>
	);
};
