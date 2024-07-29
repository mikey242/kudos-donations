import React from 'react';
import { useFormContext } from 'react-hook-form';
import { get, uniqueId } from 'lodash';
import { clsx } from 'clsx';

const CheckboxControl = ({ name, validation, label, help, isDisabled }) => {
	const {
		register,
		formState: { errors },
	} = useFormContext();

	const error = get(errors, name);
	const id = uniqueId(name + '-');

	return (
		<div className={clsx(isDisabled && 'opacity-50', 'first:mt-0 mt-3')}>
			<div className="relative flex items-center">
				<div className="flex items-center h-5">
					<input
						{...register(name, validation)}
						id={id}
						type="checkbox"
						disabled={isDisabled}
						className="disabled:cursor-not-allowed transition focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
						aria-invalid={!!error}
						aria-errormessage={`${id}-error`}
					/>
				</div>
				<div className="ml-3 text-sm">
					<label htmlFor={id} className="font-medium text-gray-700">
						{label}
					</label>
				</div>
			</div>
			{help && (
				<p className="text-sm leading-5 text-gray-500 mt-2">{help}</p>
			)}
			{error?.message && (
				<p className="mt-2 text-sm text-red-600" id={`${id}-error`}>
					{error?.message}
				</p>
			)}
		</div>
	);
};

export { CheckboxControl };