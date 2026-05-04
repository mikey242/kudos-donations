import React from 'react';
import { BaseController } from './BaseController';
import { clsx } from 'clsx';
import { Select } from '@headlessui/react';
import { RegisterOptions, useFormContext } from 'react-hook-form';
import { useEffect } from '@wordpress/element';

export interface SelectOption {
	label: string;
	value: string;
}

interface SelectControlProps {
	name: string;
	rules?: RegisterOptions;
	options: SelectOption[];
	placeholder?: string;
	isDisabled?: boolean;
	help?: string;
	ariaLabel?: string;
}

export const SelectControl = ({
	name,
	rules,
	options,
	placeholder,
	isDisabled,
	help,
	ariaLabel,
}: SelectControlProps) => {
	const { setValue } = useFormContext();
	const isSingle = options.length === 1;

	useEffect(() => {
		if (isSingle) {
			setValue(name, options[0].value);
		}
	}, [isSingle, name, options, setValue]);

	return (
		<BaseController
			name={name}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ error, field: { onChange, onBlur, value } }) => (
				<Select
					disabled={isDisabled || isSingle}
					value={value ?? ''}
					onChange={onChange}
					onBlur={onBlur}
					className={clsx(
						'control mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md',
						'disabled:cursor-not-allowed disabled:bg-slate-100',
						isSingle && 'bg-none',
						error?.message
							? 'border-red-600 text-red-900 focus:ring-red-500 focus:border-red-500'
							: 'border-gray-300 focus:ring-primary focus:border-primary'
					)}
					aria-invalid={!!error}
					aria-errormessage={
						error?.ref?.name ? `${error.ref.name}-error` : undefined
					}
					aria-label={ariaLabel ?? placeholder}
				>
					{isSingle ? (
						<option value={options[0].value}>
							{`${placeholder}: ${options[0].label}`}
						</option>
					) : (
						<>
							{placeholder && (
								<option
									disabled
									key={`placeholder_${name}`}
									value=""
								>
									{placeholder}
								</option>
							)}
							{options.map((entry) => (
								<option key={entry.value} value={entry.value}>
									{entry.label}
								</option>
							))}
						</>
					)}
				</Select>
			)}
		/>
	);
};
