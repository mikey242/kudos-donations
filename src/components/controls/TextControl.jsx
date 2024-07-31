import React from 'react';
import { clsx } from 'clsx';
import { Field } from './Field';

const TextControl = ({
	name,
	validation,
	isDisabled,
	isReadOnly,
	label,
	help,
	addOn,
	type = 'text',
	placeholder,
	inlineButton,
}) => {
	return (
		<Field
			name={name}
			type={type}
			isDisabled={isDisabled}
			help={help}
			label={label}
			validation={validation}
			render={({ id, onChange, value, error }) => (
				<>
					<div className="relative flex flex-row rounded-md">
						{addOn && (
							<div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
								<span className="text-gray-500 sm:text-sm">
									{addOn}
								</span>
							</div>
						)}
						<input
							readOnly={isReadOnly}
							disabled={isDisabled}
							type={type}
							id={id}
							value={value ?? ''}
							name={name}
							onChange={onChange}
							className={clsx(
								'read-only:read-only:bg-gray-50 disabled:cursor-not-allowed form-input transition ease-in-out block w-full pr-10 focus:outline-none sm:text-sm shadow-sm rounded-md placeholder:text-gray-500',
								error?.message
									? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500 '
									: 'border-gray-300 focus:ring-primary focus:border-primary',
								addOn && 'pl-7'
							)}
							placeholder={placeholder}
							aria-invalid={!!error}
							aria-errormessage={error?.message}
						/>
						{inlineButton && (
							<div className="ml-3 flex items-center">
								{inlineButton}
							</div>
						)}
					</div>
				</>
			)}
		/>
	);
};

export { TextControl };
