import React from 'react';
import { Field } from './Field';
import { useFormContext } from 'react-hook-form';

const SelectControl = ({
	name,
	label,
	validation,
	options,
	placeholder,
	isDisabled,
	help,
	hideLabel,
}) => {
	const { register } = useFormContext();

	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			hideLabel={hideLabel}
			help={help}
			label={label}
			render={({ id, error }) => (
				<select
					{...register(name, {
						...validation,
						disabled: isDisabled,
					})}
					id={id}
					disabled={isDisabled}
					className="disabled:cursor-not-allowed mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
					aria-invalid={!!error}
					aria-errormessage={error?.message}
				>
					{placeholder && (
						<option disabled key={`placeholder_${name}`} value="">
							{placeholder}
						</option>
					)}
					{options.map((entry, index) => (
						<option key={index} value={entry.value}>
							{entry.label}
						</option>
					))}
				</select>
			)}
		/>
	);
};

export { SelectControl };
