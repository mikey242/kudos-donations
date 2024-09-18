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

const RadioGroupControl = ({
	name,
	options,
	help,
	label,
	isDisabled,
	rules,
}) => {
	const id = _.uniqueId('kudos');

	return (
		<Controller
			name={name}
			rules={isDisabled ? {} : rules}
			disabled={isDisabled}
			render={({ field: { onChange, value } }) => (
				<BaseControl
					label={label}
					help={help}
					id={id}
					className="kudos-button-group"
				>
					<div>
						<ToggleGroupControl isBlock label="Label" value={value}>
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
			)}
		/>
	);
};

export { RadioGroupControl };
