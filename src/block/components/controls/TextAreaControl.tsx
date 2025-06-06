import React from 'react';
import { BaseController } from './BaseController';
import { clsx } from 'clsx';
import { Textarea } from '@headlessui/react';

interface TextAreaControlProps {
	name: string;
	rules?: any;
	label?: string;
	help?: string;
	placeholder?: string;
	isDisabled?: boolean;
	ariaLabel?: string;
}

export const TextAreaControl = ({
	name,
	label,
	rules,
	placeholder,
	help,
	isDisabled,
	ariaLabel,
}: TextAreaControlProps) => {
	return (
		<BaseController
			name={name}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ error, field }) => (
				<div className="mt-1">
					<Textarea
						{...field}
						disabled={isDisabled}
						rows={4}
						name={name}
						placeholder={placeholder}
						aria-label={ariaLabel ?? label ?? placeholder}
						className={clsx(
							// General
							'control shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md',
							// Disabled
							'disabled:cursor-not-allowed disabled:bg-slate-100',
							// Read only
							'read-only:bg-slate-50'
						)}
						aria-invalid={!!error}
						aria-errormessage={error?.message}
					/>
				</div>
			)}
		/>
	);
};
