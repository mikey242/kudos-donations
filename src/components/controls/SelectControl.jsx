import React from 'react';
import { useFormContext } from 'react-hook-form';
import { get, uniqueId } from 'lodash';

const SelectControl = ({ name, label, validation, options, placeholder }) => {
	const {
		register,
		formState: { errors },
	} = useFormContext();

	const error = get(errors, name);
	const id = uniqueId(name + '-');

	return (
		<div className="first:mt-0 mt-3">
			<label
				htmlFor={id}
				className={
					label ? 'block text-sm font-bold text-gray-700' : 'sr-only'
				}
			>
				{label}
			</label>
			<select
				{...register(name, validation)}
				id={id}
				defaultValue=""
				className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
				aria-invalid={!!error}
				aria-errormessage={`${id}-error`}
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
			{error?.message && (
				<p className="mt-2 text-sm text-red-600" id={`${id}-error`}>
					{error?.message}
				</p>
			)}
		</div>
	);
};

export { SelectControl };