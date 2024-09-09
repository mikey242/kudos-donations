import React from 'react';
import { clsx } from 'clsx';
import { Field } from './Field';
import { useFormContext } from 'react-hook-form';
import { useEffect, useRef, useState } from '@wordpress/element';
import { get, uniqueId } from 'lodash';

export const TextControl = ({
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
	const addonRef = useRef(null);
	const [width, setWidth] = useState(0);
	useEffect(() => {
		if (addonRef.current) {
			setWidth(addonRef.current?.offsetWidth);
		}
	}, [addOn]);
	return (
		<Field
			name={name}
			type={type}
			isDisabled={isDisabled}
			help={help}
			label={label}
			validation={validation}
			render={({ id, error, onChange, value }) => (
				<>
					<div className="relative flex flex-row rounded-md">
						{addOn && (
							<div className="absolute inset-y-0 start-0 top-0 ps-3.5 flex items-center pointer-events-none">
								<span
									ref={addonRef}
									className="text-gray-500 sm:text-sm"
								>
									{addOn}
								</span>
							</div>
						)}
						<input
							readOnly={isReadOnly}
							disabled={isDisabled}
							type={type}
							id={id}
							name={name}
							onChange={onChange}
							value={value ?? ''}
							className={clsx(
								// General
								'form-input transition ease-in-out block w-full pr-10 focus:outline-none sm:text-sm shadow-sm rounded-md placeholder:text-gray-500',
								// Disabled
								'disabled:cursor-not-allowed disabled:bg-slate-100',
								// Read only
								'read-only:bg-slate-50',
								// Invalid
								error?.message
									? 'border-red-600 text-red-900 focus:ring-red-500 focus:border-red-500'
									: 'border-gray-300 focus:ring-primary focus:border-primary'
							)}
							style={
								addOn && {
									paddingLeft: width + 18 + 'px',
								}
							}
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

export const InlineTextEdit = ({
	name,
	isDisabled,
	className,
	type = 'text',
	placeholder,
}) => {
	const {
		formState: { errors },
	} = useFormContext();
	const [id] = useState(uniqueId(name + '-'));
	const error = get(errors, name);

	return (
		<>
			<input
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
				aria-errormessage={error?.message}
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
