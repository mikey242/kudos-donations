import { useId } from '@wordpress/element';
import { BaseControl, ControlProps } from './BaseControl';
import {
	BaseControl as WPBaseControl,
	FormTokenField as WPFormTokenField,
} from '@wordpress/components';
import React from 'react';

interface FormTokenFieldProps extends ControlProps {
	maxLength: number;
}

export const FormTokenField = ({
	name,
	rules,
	label,
	help,
	isDisabled,
	maxLength,
}: FormTokenFieldProps): React.ReactNode => {
	const id = useId();

	return (
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, onBlur, value, description }) => (
				<WPBaseControl
					help={description}
					id={id}
					className="kudos-button-group"
					__nextHasNoMarginBottom
				>
					<WPFormTokenField
						__next40pxDefaultSize
						__experimentalShowHowTo={false}
						tokenizeOnBlur
						tokenizeOnSpace
						maxLength={maxLength}
						label={label}
						disabled={isDisabled}
						onChange={onChange}
						onBlur={onBlur}
						value={value ?? []}
						__nextHasNoMarginBottom
					/>
				</WPBaseControl>
			)}
		/>
	);
};
