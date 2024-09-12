import React from 'react';
import { clsx } from 'clsx';
import { get } from 'lodash';
import { Controller, useFormContext } from 'react-hook-form';

export const BaseController = ({
	children,
	isDisabled,
	type,
	help,
	name,
	render,
	rules,
}) => {
	const {
		formState: { errors },
		control,
	} = useFormContext();

	const error = get(errors, name);

	return (
		<div className="first:mt-0 mt-3">
			<div
				className={clsx(
					'form-element text-slate-800',
					'hidden' === type && 'hidden',
					isDisabled && 'opacity-50'
				)}
			>
				{/* React-Hook-Form's controller for registering with form. */}
				<Controller
					control={control}
					name={name}
					rules={isDisabled ? {} : rules}
					disabled={isDisabled}
					render={({ field }) => (
						<>{render ? render({ error, field }) : children}</>
					)}
				/>
			</div>
			{/* Errors need to be shown outside hidden element. */}
			{error ? <Error error={error} /> : <Help>{help}</Help>}
		</div>
	);
};

const Help = ({ children }) => {
	return (
		<>
			{children && (
				<p className="xs leading-5 text-gray-500 mt-2">{children}</p>
			)}
		</>
	);
};

const Error = ({ error }) => {
	return (
		<>
			{error?.message && (
				<p
					role="alert"
					className="mt-2 text-left text-sm text-red-600"
					id={`${error?.ref?.name}-error`}
				>
					{error.message}
				</p>
			)}
		</>
	);
};
