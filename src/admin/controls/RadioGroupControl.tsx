import React from 'react';
import { useId } from '@wordpress/element';
import {
	BaseControl as WPBaseControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControl as ToggleGroupControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

export interface RadioGroupOption {
	label: string;
	value: string;
	disabled?: boolean;
}

interface RadioGroupControlProps extends ControlProps {
	options: RadioGroupOption[];
}

interface RadioGroupControlBaseProps {
	value: string;
	label?: string;
	help?: string;
	options: RadioGroupOption[];
	onChange: (value: string) => void;
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
		<BaseControl
			name={name}
			rules={rules}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<RadioGroupControlBase
					label={label}
					value={value}
					help={description}
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
}: RadioGroupControlBaseProps): React.ReactNode => {
	const id = useId();

	return (
		<WPBaseControl help={help} id={id} className="kudos-button-group">
			<div>
				<ToggleGroupControl
					isBlock
					value={value}
					label={label}
					__next40pxDefaultSize
				>
					{options.map((option: RadioGroupOption) => (
						<ToggleGroupControlOption
							key={option.value}
							label={option.label}
							value={option.value}
							disabled={option.disabled ?? false}
							onClick={() => onChange(option.value)}
						/>
					))}
				</ToggleGroupControl>
			</div>
		</WPBaseControl>
	);
};
