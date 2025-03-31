import React from 'react';
import { Label, Radio, RadioGroup } from '@headlessui/react';
import { clsx } from 'clsx';
import { BaseController } from './BaseController';
import { useCallback, useRef } from '@wordpress/element';

export const RadioGroupControl = ({
	name,
	options,
	help,
	isDisabled,
	rules,
	label,
	ariaLabel,
}) => {
	// Create refs for each radio button to handle focus programmatically
	const radioRefs = useRef([]);
	// Helper function to handle keyboard events for custom navigation.
	const handleKeyDown = useCallback(
		(event, onChange, value) => {
			// Find the current index of the selected option.
			const currentIndex = options.findIndex(
				(option) => option.value === value
			);

			let newIndex = currentIndex;

			// Update the index based on the arrow key pressed.
			if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
				newIndex = (currentIndex + 1) % options.length;
			} else if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
				newIndex = (currentIndex - 1 + options.length) % options.length;
			} else {
				// If the key is not an arrow key, do nothing.
				return;
			}

			// Prevent the default arrow key behavior.
			event.preventDefault();

			// Update the selection to the new index.
			onChange(options[newIndex].value);
			// Focus the radio button at the new index
			if (radioRefs.current[newIndex]) {
				radioRefs.current[newIndex].focus();
			}
		},
		[options]
	);

	return (
		<BaseController
			name={name}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ field: { onChange, value } }) => {
				return (
					<RadioGroup
						value={value ?? ''}
						onChange={onChange}
						disabled={isDisabled}
						className="first:mt-0 mt-3 grid gap-3 grid-flow-row xs:grid-flow-col xs:auto-cols-fr"
						aria-label={ariaLabel ?? label}
						onKeyDown={(event) =>
							handleKeyDown(event, onChange, value)
						}
					>
						{options.map((option, index) => (
							<Radio
								key={option.value}
								value={option.value}
								disabled={option.disabled}
								ref={(el) => (radioRefs.current[index] = el)}
								className={({ checked }) =>
									clsx(
										'control focus:ring-2 focus:ring-offset-2 focus:ring-primary',
										checked
											? 'bg-primary border-transparent text-white font-bold'
											: 'bg-white border-gray-300 text-slate-800 hover:bg-gray-50',
										option.disabled
											? 'opacity-50 cursor-not-allowed'
											: 'cursor-pointer',
										'transition ease-in-out focus:outline-none border rounded-md py-2 px-2 sm:py-3 sm:px-3 flex items-center justify-center text-sm font-medium sm:flex-1'
									)
								}
							>
								<Label as="p">{option.label}</Label>
							</Radio>
						))}
					</RadioGroup>
				);
			}}
		/>
	);
};
