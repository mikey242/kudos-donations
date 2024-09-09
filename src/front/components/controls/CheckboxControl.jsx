import React from 'react';
import { Field } from './Field';

const CheckboxControl = ({
	name,
	validation,
	label,
	altLabel,
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
			render={({ id, error, onChange, value }) => (
				<div className="relative flex items-center">
					<div className="flex items-center h-5">
						<input
							disabled={isDisabled}
							id={id}
							checked={value}
							onChange={onChange}
							name={name}
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
