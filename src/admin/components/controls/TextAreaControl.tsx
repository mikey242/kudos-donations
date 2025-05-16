import React, { forwardRef } from 'react';
import { TextareaControl as WPTextAreaControl } from '@wordpress/components';
import { BaseControl, ControlProps } from './BaseControl';

interface TextAreaControlProps extends ControlProps {
	id?: string;
	hideLabelFromVision?: boolean;
}

const TextAreaControl = forwardRef<HTMLTextAreaElement, TextAreaControlProps>(
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
