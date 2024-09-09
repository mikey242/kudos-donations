import React from 'react';
import { ToggleControl as WPToggleControl } from '@wordpress/components';
import { BaseControl } from './BaseControl';

const ToggleControl = ({ name, validation, label, help, isDisabled }) => {
	return (
		<BaseControl
			name={name}
			rules={validation}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<WPToggleControl
					label={label}
					checked={value ?? false}
					onChange={onChange}
					disabled={isDisabled}
					help={description}
				/>
			)}
		/>
	);
};

export { ToggleControl };
