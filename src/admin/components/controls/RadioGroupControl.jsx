import React from 'react';
import { Controller } from 'react-hook-form';
import {
	BaseControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControl as ToggleGroupControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import _ from 'lodash';

const id = _.uniqueId('kudos');

export const RadioGroupControl = ({
	name,
	options,
	help,
	label,
	isDisabled,
	rules,
}) => {
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
				>
					{options.map((option) => {
						return (
							<ToggleGroupControlOption
								key={option.value}
								label={option.label}
								value={option.value}
								onClick={() => onChange(option.value)}
							/>
						);
					})}
				</ToggleGroupControl>
			</div>
		</BaseControl>
	);
};
