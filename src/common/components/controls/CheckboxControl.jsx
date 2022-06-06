import React from 'react';
import { useFormContext } from 'react-hook-form';
import { get } from 'lodash';

const CheckboxControl = ({ name, validation, label, help }) => {
	const {
		register,
		formState: { errors },
	} = useFormContext();

	const error = get(errors, name);

	return (
		<div className="first:mt-0 mt-3">
			{help && (
				<p className="text-sm leading-5 text-gray-500 mb-1">{help}</p>
			)}
			<div className="relative flex items-center">
				<div className="flex items-center h-5">
					<input
						{...register(name, validation)}
						id={name}
						type="checkbox"
						className="transition focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
					/>
				</div>
				<div className="ml-3 text-sm">
					<label htmlFor={name} className="font-medium text-gray-700">
						{label}
					</label>
				</div>
			</div>
			{error?.message && (
				<p className="mt-2 text-sm text-red-600">{error?.message}</p>
			)}
		</div>
	);
};

export { CheckboxControl };
