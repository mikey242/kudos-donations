import React from 'react';
import { BaseController } from './BaseController';
import { clsx } from 'clsx';
import { Textarea } from '@headlessui/react';

export const TextAreaControl = ({
	name,
	rules,
	placeholder,
	help,
	isDisabled,
}) => {
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
						className={clsx(
							// General
							'shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md',
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
