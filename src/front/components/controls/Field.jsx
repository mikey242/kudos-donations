import React from 'react';
import { clsx } from 'clsx';
import { get, uniqueId } from 'lodash';
import { Controller, useFormContext } from 'react-hook-form';
import { useState } from '@wordpress/element';

export const Field = ({
	children,
	isDisabled,
	type,
	help,
	name,
	render,
	validation,
}) => {
	const {
		formState: { errors },
	} = useFormContext();
	const [id] = useState(uniqueId(name + '-'));

	const error = get(errors, name);

	return (
		<div className="first:mt-0 mt-3">
			<div className={clsx('hidden' === type && 'hidden')}>
				<div
					className={clsx(
						'md:col-span-4',
						isDisabled && 'opacity-50'
					)}
				>
					{/* React-Hook-Form's controller for registering with form. */}
					<Controller
						name={name}
						rules={isDisabled ? {} : validation}
						disabled={isDisabled}
						render={({ field: { onChange, value } }) => (
							<>
								{render
									? render({ id, error, onChange, value })
									: children}
							</>
						)}
					/>
				</div>
			</div>
			{/* Errors need to be shown outside hidden element. */}
			{error ? <Error error={error} /> : <Help>{help}</Help>}
		</div>
	);
};

export const Help = ({ children }) => {
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
