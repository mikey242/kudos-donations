import { BaseControl } from './BaseControl';
import {
	BaseControl as WPBaseControl,
	FormTokenField as WPFormTokenField,
} from '@wordpress/components';
import _ from 'lodash';

export const FormTokenField = ({
	name,
	validation,
	label,
	help,
	isDisabled,
	maxLength,
}) => {
	const id = _.uniqueId('kudos');

	return (
		<BaseControl
			name={name}
			rules={validation}
			isDisabled={isDisabled}
			help={help}
			render={({ onChange, value, description }) => (
				<WPBaseControl
					help={description}
					id={id}
					className="kudos-button-group"
				>
					<WPFormTokenField
						__next40pxDefaultSize
						__experimentalShowHowTo={false}
						tokenizeOnBlur
						tokenizeOnSpace
						maxLength={maxLength}
						label={label}
						disabled={isDisabled}
						onChange={onChange}
						value={value ?? []}
					/>
				</WPBaseControl>
			)}
		/>
	);
};
