import React from 'react';
import { Field } from './Field';

const CheckboxControl = ({ name, validation, label, help, isDisabled }) => {
	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			help={help}
			validation={validation}
			render={({ id, error, onChange, value }) => (
				<div className="relative flex items-center">
					<div className="flex items-center h-5">
						<input
							disabled={isDisabled}
							id={id}
							checked={value ?? false}
							onChange={onChange}
							name={name}
							type="checkbox"
							className="disabled:cursor-not-allowed transition focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
							aria-invalid={!!error}
							aria-errormessage={error?.message}
						/>
					</div>
					{label && (
						<div className="ml-3 text-sm">
							<label
								htmlFor={id}
								className="font-medium text-gray-700"
							>
								{label}
							</label>
						</div>
					)}
				</div>
			)}
		/>
	);
};

export { CheckboxControl };
