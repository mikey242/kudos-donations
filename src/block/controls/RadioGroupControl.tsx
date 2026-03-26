import React from 'react';
import { Label, Radio, RadioGroup } from '@headlessui/react';
import { clsx } from 'clsx';
import { BaseController } from './BaseController';
import { RegisterOptions } from 'react-hook-form';

export interface RadioGroupOption {
	label: string;
	value: string;
	disabled?: boolean;
}

interface RadioGroupControlProps {
	name: string;
	options: RadioGroupOption[];
	help?: string;
	isDisabled?: boolean;
	rules?: RegisterOptions;
	label?: string;
	ariaLabel: string;
}

export const RadioGroupControl = ({
	name,
	options,
	help,
	isDisabled,
	rules,
	label,
	ariaLabel,
}: RadioGroupControlProps) => {
	return (
		<BaseController
			name={name}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ field: { onChange, value } }) => (
				<RadioGroup
					value={value ?? ''}
					onChange={onChange}
					disabled={isDisabled}
					className="first:mt-0 mt-3 grid gap-3 grid-flow-row xs:grid-flow-col xs:auto-cols-fr"
					aria-label={ariaLabel ?? label}
				>
					{options.map((option: RadioGroupOption) => (
						<Radio
							key={option.value}
							value={option.value}
							disabled={option.disabled}
							className={({ checked }) =>
								clsx(
									'control focus:ring-2 focus:ring-offset-2 focus:ring-primary',
									checked
										? 'bg-primary border-transparent text-white font-bold'
										: 'bg-white border-gray-300 text-slate-800 hover:bg-gray-50',
									option.disabled
										? 'opacity-50 cursor-not-allowed'
										: 'cursor-pointer',
									'transition ease-in-out focus:outline-none border rounded-md py-2 px-2 sm:py-3 sm:px-3 flex items-center justify-center text-sm font-medium sm:flex-1'
								)
							}
						>
							<Label as="p">{option.label}</Label>
						</Radio>
					))}
				</RadioGroup>
			)}
		/>
	);
};
