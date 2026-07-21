import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControlPrefixWrapper as InputControlPrefixWrapper,
	SelectControl as WPSelectControl,
} from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

interface SelectOption {
	label: string;
	value: string;
}

interface SelectControlProps extends ControlProps {
	options: SelectOption[];
	prefix?: React.ReactNode;
}

export const SelectControl = ({
	name,
	rules,
	label,
	help,
	isDisabled,
	options,
	prefix,
}: SelectControlProps): React.ReactNode => {
	return (
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, onBlur, value, description }) => {
				const isValid = options.some(
					(option) => option.value === value
				);
				return (
					<WPSelectControl
						label={label}
						onChange={onChange}
						onBlur={onBlur}
						disabled={isDisabled}
						help={description}
						value={isValid ? value : ''}
						prefix={
							prefix && (
								<InputControlPrefixWrapper>
									{prefix}
								</InputControlPrefixWrapper>
							)
						}
						options={options}
					/>
				);
			}}
		/>
	);
};
