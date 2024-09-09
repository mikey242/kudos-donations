import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControlPrefixWrapper as InputControlPrefixWrapper,
	SelectControl as WPSelectControl,
} from '@wordpress/components';
import { BaseControl } from './BaseControl';

const SelectControl = ({
	name,
	validation,
	label,
	help,
	isDisabled,
	options,
	prefix,
}) => {
	return (
		<BaseControl
			name={name}
			rules={validation}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<WPSelectControl
					label={label}
					checked={value ?? false}
					onChange={onChange}
					disabled={isDisabled}
					help={description}
					value={value}
					prefix={
						prefix && (
							<InputControlPrefixWrapper>
								{prefix}
							</InputControlPrefixWrapper>
						)
					}
					options={options}
					__next40pxDefaultSize
				/>
			)}
		/>
	);
};

export { SelectControl };
