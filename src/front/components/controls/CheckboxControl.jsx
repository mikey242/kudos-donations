import React from 'react';
import { BaseController } from './BaseController';

export const CheckboxControl = ({ name, rules, label, help, isDisabled }) => {
	return (
		<BaseController
			name={name}
			isDisabled={isDisabled}
			help={help}
			rules={rules}
			render={({ error, field: { onChange, value } }) => (
				// eslint-disable-next-line jsx-a11y/label-has-associated-control
				<label className="relative flex items-center">
					<div className="flex items-center h-5">
						<input
							disabled={isDisabled}
							checked={value ?? false}
							onChange={onChange}
							name={name}
							type="checkbox"
							className="disabled:cursor-not-allowed transition focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
							aria-invalid={!!error}
							aria-errormessage={error?.message}
						/>
					</div>
					{label && (
						<div className="ml-3 text-sm">
							<span className="font-medium text-gray-700">
								{label}
							</span>
						</div>
					)}
				</label>
			)}
		/>
	);
};
