import React, { useId } from 'react';
import { Controller } from 'react-hook-form';
import {
	BaseControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControl as ToggleGroupControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { ControlProps } from './BaseControl';

export interface RadioGroupOption {
	label: string;
	value: string;
	disabled?: boolean;
}

interface RadioGroupControlProps extends ControlProps {
	options: RadioGroupOption[];
}

export const RadioGroupControl = ({
	name,
	options,
	help,
	label,
	isDisabled,
	rules,
}: RadioGroupControlProps): React.ReactNode => {
	return (
		<Controller
			name={name}
			rules={isDisabled ? {} : rules}
			disabled={isDisabled}
			render={({ field: { onChange, value } }) => (
				<RadioGroupControlBase
					label={label}
					value={value}
					help={help}
					onChange={onChange}
					options={options}
				/>
			)}
		/>
	);
};

export const RadioGroupControlBase = ({
	value,
	label,
	help,
	options,
	onChange,
}) => {
	const id = useId();

	return (
		<BaseControl
			help={help}
			id={id}
			className="kudos-button-group"
			__nextHasNoMarginBottom
		>
			<div>
				<ToggleGroupControl
					isBlock
					value={value}
					label={label}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				>
					{options.map((option: RadioGroupOption) => {
						return (
							<ToggleGroupControlOption
								key={option.value}
								label={option.label}
								value={option.value}
								disabled={option.disabled ?? false}
								onClick={() => onChange(option.value)}
							/>
						);
					})}
				</ToggleGroupControl>
			</div>
		</BaseControl>
	);
};
