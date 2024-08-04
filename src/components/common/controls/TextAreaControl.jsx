import React from 'react';
import { Field } from './Field';
import { useFormContext } from 'react-hook-form';
import { clsx } from 'clsx';

const TextAreaControl = ({
	name,
	validation,
	placeholder,
	label,
	hideLabel,
	help,
	isDisabled,
}) => {
	const { register } = useFormContext();

	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			help={help}
			label={label}
			hideLabel={hideLabel}
			render={({ id, error }) => (
				<div className="mt-1">
					<textarea
						{...register(name, {
							...validation,
							disabled: isDisabled,
						})}
						rows={4}
						id={id}
						placeholder={placeholder}
						className={clsx(
							// General
							'shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md',
							// Disabled
							'disabled:cursor-not-allowed',
							// Read only
							'read-only:bg-gray-100 read-only:cursor-not-allowed'
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
