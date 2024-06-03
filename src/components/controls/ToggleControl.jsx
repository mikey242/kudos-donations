import React from 'react';
import { Controller } from 'react-hook-form';
import { Switch } from '@headlessui/react';
import { clsx } from 'clsx';

const ToggleControl = ({ name, validation, label, help, disabled }) => {
	return (
		<Controller
			name={name}
			rules={validation}
			render={({ field: { value, onChange } }) => (
				<div>
					<Switch.Group
						as="div"
						className="flex items-center first:mt-0 mt-3 mb-1"
					>
						<Switch
							checked={value}
							onChange={onChange}
							disabled={disabled}
							className={clsx(
								value ? 'bg-primary' : 'bg-gray-200',
								disabled && 'opacity-50',
								'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary'
							)}
						>
							<span
								aria-hidden="true"
								className={clsx(
									value ? 'translate-x-5' : 'translate-x-0',
									'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200'
								)}
							/>
						</Switch>
						{label && (
							<Switch.Label
								className={clsx(
									disabled && 'opacity-50',
									'ml-3 cursor-pointer'
								)}
							>
								<span className="text-sm font-bold text-gray-700">
									{label}
								</span>
							</Switch.Label>
						)}
					</Switch.Group>
					{help && (
						<p
							className={clsx(
								disabled && 'opacity-50',
								'mt-2 text-sm leading-5 text-gray-500'
							)}
						>
							{help}
						</p>
					)}
				</div>
			)}
		/>
	);
};

export { ToggleControl };
