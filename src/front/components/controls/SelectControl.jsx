import React from 'react';
import { BaseController } from './BaseController';
import { clsx } from 'clsx';
import { Select } from '@headlessui/react';

export const SelectControl = ({
	name,
	rules,
	options,
	placeholder,
	isDisabled,
	help,
	ariaLabel,
}) => {
	return (
		<BaseController
			name={name}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ error, field: { onChange, value } }) => (
				<Select
					disabled={isDisabled}
					value={value ?? ''}
					onChange={onChange}
					className={clsx(
						// General
						'mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md',
						// Disabled
						'disabled:cursor-not-allowed disabled:bg-slate-100',
						// Invalid
						error?.message
							? 'border-red-600 text-red-900 focus:ring-red-500 focus:border-red-500'
							: 'border-gray-300 focus:ring-primary focus:border-primary'
					)}
					aria-invalid={!!error}
					aria-errormessage={error?.message}
					aria-label={ariaLabel ?? placeholder}
				>
					{placeholder && (
						<option disabled key={`placeholder_${name}`} value="">
							{placeholder}
						</option>
					)}
					{options.map((entry) => (
						<option key={entry.value} value={entry.value}>
							{entry.label}
						</option>
					))}
				</Select>
			)}
		/>
	);
};
