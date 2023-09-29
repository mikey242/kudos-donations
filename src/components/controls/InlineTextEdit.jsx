import React from 'react';
import { useFormContext } from 'react-hook-form';
import classNames from 'classnames';
import { get, uniqueId } from 'lodash';

const InlineTextEdit = ({
	name,
	validation,
	disabled,
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
				disabled={disabled}
				className={classNames(
					error?.message
						? 'border-red-300 text-red-900 placeholder-red-300'
						: 'border-0 focus:ring-primary focus:border-primary',
					disabled && 'cursor-not-allowed opacity-75',
					'hover:bg-zinc-50 hover:shadow-inner focus:text-gray-900 bg-transparent form-input transition ease-in-out inline focus:outline-none sm:text-sm rounded-md',
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

export { InlineTextEdit };
