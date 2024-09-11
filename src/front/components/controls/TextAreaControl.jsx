import React from 'react';
import { Field } from './Field';
import { clsx } from 'clsx';

const TextAreaControl = ({
	name,
	validation,
	placeholder,
	help,
	isDisabled,
}) => {
	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			help={help}
			validation={validation}
			render={({ id, error, onChange, value }) => (
				<div className="mt-1">
					<textarea
						value={value ?? ''}
						onChange={onChange}
						disabled={isDisabled}
						rows={4}
						id={id}
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

export { TextAreaControl };
