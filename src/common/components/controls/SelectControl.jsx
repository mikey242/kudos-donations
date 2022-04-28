import React from 'react';
import { useFormContext } from 'react-hook-form';

const SelectControl = ({ name, label, validation, options, placeholder }) => {
	const {
		register,
		formState: { errors },
	} = useFormContext();

	return (
		<div className="first:mt-0 mt-3">
			<label
				htmlFor={name}
				className={
					label
						? 'block text-sm font-medium text-gray-700'
						: 'sr-only'
				}
			>
				{label}
			</label>
			<select
				{...register(name, validation)}
				defaultValue=""
				className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
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
			{errors[name]?.message && (
				<p className="mt-2 text-sm text-red-600" id="email-error">
					{errors[name]?.message}
				</p>
			)}
		</div>
	);
};

export { SelectControl };
