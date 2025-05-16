import React, { ReactNode } from 'react';
import { Controller, RegisterOptions, useFormContext } from 'react-hook-form';
import { get } from 'lodash';
import { clsx } from 'clsx';

export interface ControlProps {
	name: string;
	label?: string;
	rules?: RegisterOptions;
	isDisabled?: boolean;
	help?: string;
}

interface BaseControlProps {
	name: string;
	rules?: RegisterOptions;
	isDisabled?: boolean;
	help?: string;
	children?: ReactNode;
	render?: (params: {
		description?: string;
		onChange: (value: any) => void;
		value: any;
	}) => ReactNode;
}

export const BaseControl = ({
	name,
	rules,
	isDisabled = false,
	help,
	render,
	children,
}: BaseControlProps): ReactNode => {
	const {
		formState: { errors },
	} = useFormContext();
	const error = get(errors, name);
	const rawMessage = error?.message;
	const description = typeof rawMessage === 'string' ? rawMessage : help;

	return (
		<Controller
			name={name}
			rules={isDisabled ? {} : rules}
			disabled={isDisabled}
			render={({ field: { onChange, value } }) => {
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
