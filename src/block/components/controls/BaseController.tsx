import React, { ReactNode, ReactElement } from 'react';
import { clsx } from 'clsx';
import { get } from 'lodash';
import {
	Controller,
	ControllerRenderProps,
	FieldError,
	FieldValues,
	RegisterOptions,
	useFormContext,
} from 'react-hook-form';

interface BaseControllerProps {
	name: string;
	type?: string;
	isDisabled?: boolean;
	help?: ReactNode;
	rules?: RegisterOptions;
	render: (params: {
		field: ControllerRenderProps<FieldValues, string>;
		error: FieldError | undefined;
	}) => ReactElement;
}

export const BaseController = ({
	isDisabled,
	type,
	help,
	name,
	render,
	rules,
}: BaseControllerProps) => {
	const {
		formState: { errors },
		control,
	} = useFormContext();

	const error = get(errors, name) as FieldError | undefined;

	return (
		<div className={clsx('field', 'field-' + name, 'first:mt-0 mt-3')}>
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
					render={({ field }) => render({ error, field })}
				/>
			</div>
			{/* Errors need to be shown outside hidden element. */}
			{error ? <ErrorMessage error={error} /> : <Help>{help}</Help>}
		</div>
	);
};

interface HelpProps {
	children?: ReactNode;
}

const Help = ({ children }: HelpProps) => {
	return (
		<>
			{children && (
				<p className="xs leading-5 text-gray-500 mt-2">{children}</p>
			)}
		</>
	);
};

interface ErrorMessageProps {
	error?: FieldError;
}

const ErrorMessage = ({ error }: ErrorMessageProps) => {
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
