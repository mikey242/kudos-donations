/* eslint-disable camelcase */
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useFormContext } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { TextAreaControl } from '../controls';
import type { Campaign } from '../../../types/posts';

interface MessageTabProps {
	campaign: Campaign;
}

export const MessageTab = ({ campaign }: MessageTabProps) => {
	const {
		meta: { message_title, message_description },
	} = campaign;
	const { setFocus } = useFormContext();

	useEffect(() => {
		setFocus('message');
	}, [setFocus]);

	return (
		<BaseTab title={message_title} description={message_description}>
			<TextAreaControl
				name="message"
				placeholder={__('Message', 'kudos-donations')}
			/>
		</BaseTab>
	);
};
