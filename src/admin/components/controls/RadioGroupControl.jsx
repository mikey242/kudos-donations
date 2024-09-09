import React from 'react';
import { Controller } from 'react-hook-form';
import { BaseControl, Button, ButtonGroup } from '@wordpress/components';
import _ from 'lodash';

const RadioGroupControl = ({
	name,
	options,
	help,
	label,
	isDisabled,
	validation,
}) => {
	const id = _.uniqueId('kudos');

	return (
		<Controller
			name={name}
			rules={isDisabled ? {} : validation}
			disabled={isDisabled}
			render={({ field: { onChange, value } }) => (
				<BaseControl
					label={label}
					help={help}
					id={id}
					className="kudos-button-group"
				>
					<div>
						<ButtonGroup>
							{options.map((option) => {
								const selected = value === option.value;
								return (
									<Button
										__next40pxDefaultSize
										type="button"
										key={option.value}
										id={id + '_' + option.label}
										variant="tertiary"
										text={option.label}
										icon={option.icon}
										isPressed={selected}
										onClick={() => onChange(option.value)}
									/>
								);
							})}
						</ButtonGroup>
					</div>
				</BaseControl>
			)}
		/>
	);
};

export { RadioGroupControl };
