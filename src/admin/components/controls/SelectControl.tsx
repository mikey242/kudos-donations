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
	value?: any;
	onChange?: (value: any) => void;
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
			render={({ onChange, value, description }) => (
				<WPSelectControl
					label={label}
					onChange={onChange}
					disabled={isDisabled}
					help={description}
					value={value !== undefined && value !== null ? value : ''}
					prefix={
						prefix && (
							<InputControlPrefixWrapper>
								{prefix}
							</InputControlPrefixWrapper>
						)
					}
					options={options}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			)}
		/>
	);
};
