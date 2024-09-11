import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControlPrefixWrapper as InputControlPrefixWrapper,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControlSuffixWrapper as InputControlSuffixWrapper,
} from '@wordpress/components';
import { BaseControl } from './BaseControl';

export const TextControl = ({
	name,
	rules,
	isDisabled,
	label,
	help,
	suffix,
	prefix,
	type = 'text',
	placeholder,
}) => {
	return (
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<>
					<InputControl
						label={label}
						isError={true}
						value={value ?? ''}
						disabled={isDisabled}
						onChange={onChange}
						help={description}
						placeholder={placeholder}
						prefix={
							prefix && (
								<InputControlPrefixWrapper>
									{prefix}
								</InputControlPrefixWrapper>
							)
						}
						type={type}
						suffix={
							suffix && (
								<InputControlSuffixWrapper>
									{suffix}
								</InputControlSuffixWrapper>
							)
						}
						__next40pxDefaultSize
					/>
				</>
			)}
		/>
	);
};
