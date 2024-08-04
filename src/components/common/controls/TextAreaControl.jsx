import React from 'react';
import { Field } from './Field';

const TextAreaControl = ({
	name,
	validation,
	placeholder,
	label,
	hideLabel,
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
			altLabel={altLabel}
			hideLabel={hideLabel}
			validation={validation}
			render={({ id, value, onChange, error }) => (
				<div className="mt-1">
					<textarea
						rows={4}
						id={id}
						value={value ?? ''}
						onChange={onChange}
						disabled={isDisabled}
						placeholder={placeholder}
						className="disabled:cursor-not-allowed shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
						aria-invalid={!!error}
						aria-errormessage={error?.message}
					/>
				</div>
			)}
		/>
	);
};

export { TextAreaControl };
