import React, { forwardRef } from 'react';
import { TextareaControl as WPTextAreaControl } from '@wordpress/components';
import { BaseControl } from './BaseControl';

const TextAreaControl = forwardRef(
	(
		{ name, rules, label, help, isDisabled, id, hideLabelFromVision },
		ref
	) => {
		return (
			<BaseControl
				name={name}
				rules={rules}
				isDisabled={isDisabled}
				help={help}
				render={({ onChange, value, description }) => (
					<WPTextAreaControl
						id={id}
						label={label}
						value={value ?? ''}
						disabled={isDisabled}
						onChange={onChange}
						help={description}
						hideLabelFromVision={hideLabelFromVision}
						ref={ref}
						__nextHasNoMarginBottom
					/>
				)}
			/>
		);
	}
);

export { TextAreaControl };
