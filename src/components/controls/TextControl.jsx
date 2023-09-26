import React from 'react';
import { useFormContext } from 'react-hook-form';
import classNames from 'classnames';
import { get, uniqueId } from 'lodash';

const TextControl = ({
	name,
	validation,
	disabled,
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
		<>
			<div
				className={classNames(
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
						{...register(name, validation)}
						type={type}
						id={id}
						disabled={disabled}
						className={classNames(
							error?.message
								? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 '
								: 'border-gray-300 focus:ring-primary focus:border-primary',
							addOn && 'pl-7',
							disabled && 'cursor-not-allowed opacity-75',
							'form-input transition ease-in-out block w-full pr-10 focus:outline-none sm:text-sm shadow-sm rounded-md'
						)}
						placeholder={placeholder}
						aria-invalid={!!error}
						aria-errormessage={`${id}-error`}
					/>
					{inlineButton && (
						<div className="ml-3 flex items-center">
							{inlineButton}
						</div>
					)}
				</div>
				{help && (
					<p className="text-sm leading-5 text-gray-500 mt-1">
						{help}
					</p>
				)}
			</div>

			{error?.message && (
				<p
					className="mt-2 text-left text-sm text-red-600"
					id={`${id}-error`}
				>
					{error?.message}
				</p>
			)}
		</>
	);
};

export { TextControl };
