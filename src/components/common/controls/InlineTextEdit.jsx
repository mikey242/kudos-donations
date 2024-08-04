import React from 'react';
import { useFormContext } from 'react-hook-form';
import { clsx } from 'clsx';
import { get, uniqueId } from 'lodash';

export const InlineTextEdit = ({
	name,
	validation,
	isDisabled,
	className,
	type = 'text',
	placeholder,
}) => {
	const {
		register,
		formState: { errors },
	} = useFormContext();

	const error = get(errors, name);
	const id = uniqueId(name + '-');

	return (
		<>
			<input
				{...register(name, validation)}
				type={type}
				id={id}
				disabled={isDisabled}
				className={clsx(
					error?.message
						? 'border-red-300 text-red-900 placeholder-red-300'
						: 'border-0 focus:ring-primary focus:border-primary',
					'disabled:cursor-not-allowed disabled:opacity-50 hover:bg-zinc-50 hover:shadow-inner focus:text-gray-900 bg-transparent form-input transition ease-in-out inline focus:outline-none text-sm rounded-md',
					className
				)}
				placeholder={placeholder}
				aria-invalid={!!error}
				aria-errormessage={`${id}-error`}
				onBlur={(e) => e.target.form.requestSubmit()}
			/>

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
