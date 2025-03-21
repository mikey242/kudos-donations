import { __ } from '@wordpress/i18n';
import React from 'react';
import { useFormContext } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { TextAreaControl } from '../controls';

export const MessageTab = ({ title, description, buttons }) => {
	const { setFocus } = useFormContext();

	useEffect(() => {
		setFocus('message');
	}, [setFocus]);

	return (
		<BaseTab title={title} description={description} buttons={buttons}>
			<TextAreaControl
				name="message"
				placeholder={__('Message', 'kudos-donations')}
			/>
		</BaseTab>
	);
};
