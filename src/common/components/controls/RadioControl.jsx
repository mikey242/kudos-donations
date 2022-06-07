import React from 'react';
import { useFormContext } from 'react-hook-form';

const RadioControl = ({ name, validation, options, label, help }) => {
	const { getValues, register } = useFormContext();

	return (
		<div className="mt-4">
			<p className="block text-sm font-bold text-gray-700">{label}</p>
			{help && <p className="text-sm leading-5 text-gray-500">{help}</p>}
			<fieldset className="mt-2">
				<legend className="sr-only">{label}</legend>
				<div className="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
					{options.map((option, index) => (
						<div key={option.id} className="flex items-center">
							<input
								{...register(name, validation)}
								id={option.id}
								value={option.id}
								type="radio"
								defaultChecked={
									!!(!getValues(name) && index === 0)
								}
								className="focus:ring-primary transition h-4 w-4 text-primary border-gray-300"
							/>
							<label
								htmlFor={option.id}
								className="ml-3 block text-sm font-medium text-gray-700"
							>
								{option.label}
							</label>
						</div>
					))}
				</div>
			</fieldset>
		</div>
	);
};

export { RadioControl };
