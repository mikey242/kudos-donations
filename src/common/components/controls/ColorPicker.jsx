import { Controller } from 'react-hook-form';
import React from 'react';
import { HexColorInput } from 'react-colorful';
import { useState } from '@wordpress/element';
import { RadioGroup } from '@headlessui/react';
import classNames from 'classnames';
import { __ } from '@wordpress/i18n';
import { PencilIcon } from '@heroicons/react/24/outline';

const ColorPicker = ({ name, label, help }) => {
	const [showPicker, setShowPicker] = useState(false);
	const togglePicker = () => {
		setShowPicker(!showPicker);
	};

	const colors = [
		{ name: 'Orange', value: '#ff9f1c', selectedColor: 'ring-primary' },
		{ name: 'Pink', value: '#ec4899', selectedColor: 'ring-pink-500' },
		{ name: 'Purple', value: '#a855f7', selectedColor: 'ring-purple-500' },
		{ name: 'Blue', value: '#3b82f6', selectedColor: 'ring-blue-500' },
		{ name: 'Green', value: '#2ec4b6', selectedColor: 'ring-green-500' },
	];

	return (
		<Controller
			name={name}
			render={({ field: { onChange, value } }) => (
				<RadioGroup
					value={value}
					onChange={onChange}
					className="first:mt-0 mt-3"
				>
					<RadioGroup.Label className="block text-sm font-bold text-gray-700">
						{label}
					</RadioGroup.Label>
					<div className="mt-2 inline-flex items-center space-x-3">
						{colors.map((color) => (
							<RadioGroup.Option
								key={color.name}
								value={color.value}
								className={({ active, checked }) =>
									classNames(
										color.selectedColor,
										active && checked
											? 'ring ring-offset-1'
											: '',
										!active && checked ? 'ring-2' : '',
										'transition -m-0.5 relative p-0.5 rounded-full flex items-center justify-center cursor-pointer focus:outline-none'
									)
								}
							>
								<RadioGroup.Label as="p" className="sr-only">
									{color.name}
								</RadioGroup.Label>
								<span
									aria-hidden="true"
									style={{ backgroundColor: color.value }}
									className="h-8 w-8 border border-black border-opacity-10 rounded-full"
								/>
							</RadioGroup.Option>
						))}
						<>
							<div className="h-5 border border-l-gray-300" />
							<RadioGroup.Option
								key="custom"
								value={value}
								className={({ active }) => {
									const checked =
										!colors.filter(
											(color) => color.value === value
										).length > 0;
									return classNames(
										active && checked
											? 'ring ring-offset-1'
											: '',
										!active && checked ? 'ring-2' : '',
										'transition -m-0.5 relative p-0.5 rounded-full flex items-center justify-center cursor-pointer focus:outline-none'
									);
								}}
							>
								<RadioGroup.Label as="p" className="sr-only">
									{__('Custom color', 'kudos-donations')}
								</RadioGroup.Label>
								<span
									onClick={togglePicker}
									aria-hidden="true"
									style={{ backgroundColor: value }}
									className="h-8 w-8 border border-black border-opacity-10 rounded-full flex justify-center items-center"
								>
									<PencilIcon className="w-5 h-5 text-white" />
								</span>
								{showPicker && (
									<div className="absolute left-full bottom-full z-1050">
										<div className="bg-white mt-2 p-5 relative rounded-lg drop-shadow-md z-[2]">
											<HexColorInput
												className={
													'w-20 border-gray-300 mt-2 placeholder-gray-500 border border-solid transition ease-in-out duration-75 leading-6 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 py-2 px-3 rounded'
												}
												color={value}
												onChange={onChange}
												prefixed
											/>
										</div>
										<button
											onClick={togglePicker}
											className="fixed top-0 left-0 w-full h-full z-1 cursor-default"
										/>
									</div>
								)}
							</RadioGroup.Option>
						</>
					</div>
					{help && (
						<p className="text-sm leading-5 text-gray-500 mt-2">
							{help}
						</p>
					)}
				</RadioGroup>
			)}
		/>
	);
};

export { ColorPicker };
