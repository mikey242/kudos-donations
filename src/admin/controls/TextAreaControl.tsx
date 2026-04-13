import React from 'react';
import { forwardRef } from '@wordpress/element';
import { TextareaControl as WPTextAreaControl } from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

interface TextAreaControlProps extends ControlProps {
	id?: string;
	hideLabelFromVision?: boolean;
}

export const TextAreaControl = forwardRef<
	HTMLTextAreaElement,
	TextAreaControlProps
>(
	(
		{
			name,
			rules,
			label,
			help,
			isDisabled,
			id,
			hideLabelFromVision,
		}: TextAreaControlProps,
		ref
	): React.ReactNode => {
		return (
			<BaseControl
				name={name}
				rules={rules}
				isDisabled={isDisabled}
				help={help}
				render={({ onChange, onBlur, value, description }) => (
					<WPTextAreaControl
						id={id}
						label={label}
						value={value ?? ''}
						disabled={isDisabled}
						onChange={onChange}
						onBlur={onBlur}
						help={description}
						hideLabelFromVision={hideLabelFromVision}
						ref={ref}
					/>
				)}
			/>
		);
	}
);
