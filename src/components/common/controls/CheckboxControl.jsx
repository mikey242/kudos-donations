import React from 'react';
import { Field } from './Field';
import { useFormContext } from 'react-hook-form';

const CheckboxControl = ({
	name,
	validation,
	label,
	altLabel,
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
			render={({ id, error }) => (
				<div className="relative flex items-center">
					<div className="flex items-center h-5">
						<input
							{...register(name, {
								...validation,
								disabled: isDisabled,
							})}
							id={id}
							type="checkbox"
							className="disabled:cursor-not-allowed transition focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
							aria-invalid={!!error}
							aria-errormessage={error?.message}
						/>
					</div>
					{altLabel && (
						<div className="ml-3 text-sm">
							<label
								htmlFor={id}
								className="font-medium text-gray-700"
							>
								{altLabel}
							</label>
						</div>
					)}
				</div>
			)}
		/>
	);
};

export { CheckboxControl };
