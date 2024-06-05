import React from 'react';
import { useFormContext } from 'react-hook-form';
import { clsx } from 'clsx';
import { get, uniqueId } from 'lodash';

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
	const {
		register,
		formState: { errors },
	} = useFormContext();

	const error = get(errors, name);
	const id = uniqueId(name + '-');

	return (
		<div
			className={clsx(
				isDisabled && 'opacity-50',
				'hidden' === type && 'hidden',
				'first:mt-0 mt-3'
			)}
		>
			<label
				htmlFor={id}
				className="block text-sm font-bold text-gray-700"
			>
				{label}
			</label>
			<div className="mt-1 relative flex rounded-md">
				{addOn && (
					<div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
						<span className="text-gray-500 sm:text-sm">
							{addOn}
						</span>
					</div>
				)}
				<input
					{...register(name, { ...validation, disabled: isDisabled })}
					readOnly={isReadOnly}
					type={type}
					id={id}
					className={clsx(
						error?.message
							? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 '
							: 'border-gray-300 focus:ring-primary focus:border-primary',
						addOn && 'pl-7',
						'read-only:read-only:bg-gray-50 disabled:cursor-not-allowed form-input transition ease-in-out block w-full pr-10 focus:outline-none sm:text-sm shadow-sm rounded-md'
					)}
					placeholder={placeholder}
					aria-invalid={!!error}
					aria-errormessage={`${id}-error`}
				/>
				{inlineButton && (
					<div className="ml-3 flex items-center">{inlineButton}</div>
				)}
			</div>
			{help && (
				<p className="text-sm leading-5 text-gray-500 mt-1">{help}</p>
			)}
			{error?.message && (
				<p
					className="mt-2 text-left text-sm text-red-600"
					id={`${id}-error`}
				>
					{error?.message}
				</p>
			)}
		</div>
	);
};

export { TextControl };
