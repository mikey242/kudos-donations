import { __ } from '@wordpress/i18n';
import React from 'react';
import { useFormContext } from 'react-hook-form';
import FormTab from './FormTab';
import { useEffect } from '@wordpress/element';
import { TextAreaControl } from '../../../common/components/controls';

const Message = (props) => {
	const { title, description, buttons } = props;

	const { register, setFocus } = useFormContext();

	useEffect(() => {
		setFocus('message');
	}, [setFocus]);

	return (
		<FormTab title={title} description={description} buttons={buttons}>
			<TextAreaControl
				name="message"
				placeholder={__('Message', 'kudos-donations')}
			/>
		</FormTab>
	);
};

export default Message;
