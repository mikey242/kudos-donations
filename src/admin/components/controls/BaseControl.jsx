import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { get } from 'lodash';
import { clsx } from 'clsx';

export const BaseControl = ({
	name,
	rules,
	isDisabled,
	help,
	render,
	children,
}) => {
	const {
		formState: { errors },
	} = useFormContext();
	const error = get(errors, name);

	return (
		<Controller
			name={name}
			rules={isDisabled ? {} : rules}
			disabled={isDisabled}
			render={({ field: { onChange, value } }) => {
				const description = error?.message ?? help;
				return (
					<div
						className={clsx(
							'kudos-base-control',
							error?.message && 'has-error'
						)}
					>
						{render
							? render({ description, onChange, value })
							: children}
					</div>
				);
			}}
		/>
	);
};
