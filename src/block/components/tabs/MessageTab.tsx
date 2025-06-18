/* eslint-disable camelcase */
import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useFormContext } from 'react-hook-form';
import BaseTab from './BaseTab';
import { useEffect } from '@wordpress/element';
import { TextAreaControl } from '../controls';
import type { Campaign } from '../../../types/entity';

interface MessageTabProps {
	campaign: Campaign;
}

export const MessageTab = ({ campaign }: MessageTabProps) => {
	const { message_title, message_description, message_required } = campaign;
	const { setFocus } = useFormContext();
	const optional = !message_required
		? '(' + __('optional', 'kudos-donations') + ')'
		: '';

	useEffect(() => {
		setFocus('message');
	}, [setFocus]);

	return (
		<BaseTab title={message_title} description={message_description}>
			<TextAreaControl
				name="message"
				rules={
					message_required && {
						required: __(
							'This field is required',
							'kudos-donations'
						),
					}
				}
				placeholder={
					// translators: %s shows (optional) when field not required.
					sprintf(__('Message %s', 'kudos-donations'), optional)
				}
			/>
		</BaseTab>
	);
};
