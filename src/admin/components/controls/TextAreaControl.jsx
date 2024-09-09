import React, { forwardRef } from 'react';
import { TextareaControl as WPTextAreaControl } from '@wordpress/components';
import { BaseControl } from './BaseControl';

const TextAreaControl = forwardRef(
	(
		{ name, validation, label, help, isDisabled, id, hideLabelFromVision },
		ref
	) => {
		return (
			<BaseControl
				name={name}
				rules={validation}
				isDisabled={isDisabled}
				help={help}
				render={({ onChange, value, description }) => (
					<WPTextAreaControl
						id={id}
						label={label}
						value={value}
						onChange={onChange}
						help={description}
						hideLabelFromVision={hideLabelFromVision}
						ref={ref}
					/>
				)}
			/>
		);
	}
);

export { TextAreaControl };
