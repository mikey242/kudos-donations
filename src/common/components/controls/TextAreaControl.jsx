import React from 'react';
import { useFormContext } from 'react-hook-form';
import { get } from 'lodash';

const TextAreaControl = ({ name, validation, placeholder, label, help }) => {
	const {
		register,
		formState: { errors },
	} = useFormContext();

	const error = get(errors, name);

	return (
		<div className="first:mt-0 mt-3">
			<label
				htmlFor={name}
				className="block text-sm font-bold text-gray-700"
			>
				{label}
			</label>
			{help && <p className="text-sm leading-5 text-gray-500">{help}</p>}
			<div className="mt-1">
				<textarea
					rows={4}
					{...register(name, validation)}
					placeholder={placeholder}
					className="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
					defaultValue={''}
					aria-invalid={!!error}
					aria-errormessage={`${name}-error`}
				/>
			</div>
			{error?.message && (
				<p className="mt-2 text-sm text-red-600" id={`${name}-error`}>
					{error?.message}
				</p>
			)}
		</div>
	);
};

export { TextAreaControl };
