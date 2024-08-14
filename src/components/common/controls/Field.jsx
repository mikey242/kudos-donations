import React from 'react';
import { clsx } from 'clsx';
import { get, uniqueId } from 'lodash';
import { Controller, useFormContext } from 'react-hook-form';
import { useState } from '@wordpress/element';

export const Field = ({
	children,
	label,
	hideLabel,
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
	const hasLabel = label && !hideLabel;

	return (
		<div className="first:mt-0 mt-3">
			<div
				className={clsx(
					'hidden' === type && 'hidden',
					label && 'grid gap-x-4 grid-cols-1 md:grid-cols-4'
				)}
			>
				<Label htmlFor={id} hideLabel={hideLabel}>
					{label}
				</Label>
				<div
					className={clsx(
						hasLabel ? 'md:col-span-3' : 'md:col-span-4',
						isDisabled && 'opacity-50'
					)}
				>
					<Controller
						name={name}
						rules={validation}
						disabled={isDisabled}
						render={({ field: { onChange, value } }) => (
							<>
								{render
									? render({ id, error, onChange, value })
									: children}
								<Help>{help}</Help>
								{hasLabel && <Error error={error} />}
							</>
						)}
					/>
				</div>
			</div>
			{!hasLabel && <Error error={error} />}
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

const Label = ({ children, htmlFor, hideLabel }) => {
	return (
		<label
			htmlFor={htmlFor}
			className={clsx(
				hideLabel
					? 'sr-only'
					: 'block text-sm font-bold text-gray-700 mb-1 md:col-span-1'
			)}
		>
			{children}
		</label>
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
