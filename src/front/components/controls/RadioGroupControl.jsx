import React from 'react';
import { RadioGroup } from '@headlessui/react';
import { clsx } from 'clsx';
import { Field } from './Field';

const RadioGroupControl = ({ name, options, help, isDisabled, validation }) => {
	return (
		<Field
			name={name}
			isDisabled={isDisabled}
			help={help}
			validation={validation}
			render={({ id, onChange, value }) => (
				<RadioGroup
					value={value ?? null}
					id={id}
					disabled={isDisabled}
					onChange={onChange}
					className="first:mt-0 mt-3"
				>
					<div className="grid gap-3 mt-1 grid-flow-row xs:grid-flow-col xs:auto-cols-fr">
						{options.map((option, i) => (
							<RadioGroup.Option
								key={i}
								value={option.value}
								className={({ active, checked }) =>
									clsx(
										active
											? 'ring-2 ring-offset-2 ring-primary'
											: '',
										checked
											? 'bg-primary border-transparent text-white font-bold'
											: 'bg-white border-gray-300 text-gray-900 hover:bg-gray-50',
										option.disabled
											? 'opacity-50 cursor-not-allowed'
											: 'cursor-pointer',
										'transition ease-in-out focus:outline-none border rounded-md py-2 px-2 sm:py-3 sm:px-3 flex items-center justify-center text-sm font-medium sm:flex-1'
									)
								}
								disabled={option.disabled}
							>
								<RadioGroup.Label as="p">
									{option.label}
								</RadioGroup.Label>
							</RadioGroup.Option>
						))}
					</div>
				</RadioGroup>
			)}
		/>
	);
};

export { RadioGroupControl };
