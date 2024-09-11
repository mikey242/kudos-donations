import React from 'react';
import { clsx } from 'clsx';
import { BaseController } from './BaseController';
import { useEffect, useRef, useState } from '@wordpress/element';
import { Input } from '@headlessui/react';

export const TextControl = ({
	name,
	rules,
	isDisabled,
	isReadOnly,
	help,
	prefix,
	type = 'text',
	placeholder,
}) => {
	const prefixRef = useRef(null);
	const [width, setWidth] = useState(0);
	useEffect(() => {
		if (prefixRef.current) {
			setWidth(prefixRef.current?.offsetWidth);
		}
	}, [prefix]);
	return (
		<BaseController
			name={name}
			type={type}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ error, field: { value, onChange } }) => (
				<>
					<div className="relative flex flex-row rounded-md">
						{prefix && (
							<div className="absolute inset-y-0 start-0 top-0 ps-3.5 flex items-center pointer-events-none">
								<span
									ref={prefixRef}
									className="text-gray-500 sm:text-sm"
								>
									{prefix}
								</span>
							</div>
						)}
						<Input
							value={value ?? ''}
							onChange={onChange}
							readOnly={isReadOnly}
							disabled={isDisabled}
							type={type}
							name={name}
							className={clsx(
								// General
								'form-input transition ease-in-out block w-full pr-10 sm:text-sm shadow-sm rounded-md placeholder:text-gray-500',
								// Focus
								'focus:outline-none',
								// Disabled
								'disabled:cursor-not-allowed disabled:bg-slate-100',
								// Invalid
								error?.message
									? 'border-red-600 text-red-900 focus:ring-red-500 focus:border-red-500'
									: 'border-gray-300 focus:ring-primary focus:border-primary'
							)}
							style={
								prefix && {
									paddingLeft: width + 18 + 'px',
								}
							}
							placeholder={placeholder}
							aria-invalid={!!error}
							aria-errormessage={error?.message}
						/>
					</div>
				</>
			)}
		/>
	);
};
