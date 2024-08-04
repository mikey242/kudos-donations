import React from 'react';
import { Field } from './Field';

const RadioControl = ({
	name,
	validation,
	options,
	label,
	help,
	isDisabled,
}) => {
	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			help={help}
			label={label}
			validation={validation}
			render={({ value, onChange }) => (
				<fieldset className="mt-2">
					<legend className="sr-only">{label}</legend>
					<div className="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
						{options.map((option) => (
							<div key={option.id} className="flex items-center">
								<input
									id={option.id}
									value={option.id}
									type="radio"
									disabled={isDisabled}
									checked={option.id === value}
									onChange={onChange}
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
			)}
		/>
	);
};

export { RadioControl };
