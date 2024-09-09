import React from 'react';
import { Switch } from '@headlessui/react';
import { clsx } from 'clsx';
import { Field } from './Field';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

const ToggleControl = ({
	name,
	validation,
	label,
	altLabel,
	help,
	isDisabled,
}) => {
	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			help={help}
			label={label}
			render={({ id, error }) => (
				<Controller
					name={name}
					rules={validation}
					disabled={isDisabled}
					defaultValue=""
					render={({ field: { value, onChange } }) => (
						<Switch.Group
							as="div"
							className="flex items-center first:mt-0 mt-3 mb-1"
						>
							<Switch
								checked={value}
								onChange={onChange}
								disabled={isDisabled}
								id={id}
								className={clsx(
									value ? 'bg-primary' : 'bg-gray-200',
									'disabled:cursor-not-allowed relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary'
								)}
								aria-invalid={!!error}
								aria-errormessage={error?.message}
							>
								<span
									aria-hidden="true"
									className={clsx(
										value
											? 'translate-x-5'
											: 'translate-x-0',
										'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200'
									)}
								/>
							</Switch>
							<Switch.Label className="ml-3 cursor-pointer">
								<span className="text-sm font-bold text-gray-700">
									{altLabel ??
										(value
											? __('On', 'kudos-donations')
											: __('Off', 'kudos-donations'))}
								</span>
							</Switch.Label>
						</Switch.Group>
					)}
				/>
			)}
		/>
	);
};

export { ToggleControl };
