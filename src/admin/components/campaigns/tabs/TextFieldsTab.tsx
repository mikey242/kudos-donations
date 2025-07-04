import { __ } from '@wordpress/i18n';
import { TextAreaControl, TextControl } from '../../controls';
import React from 'react';
import { Panel } from '../../Panel';

export const TextFieldsTab = () => {
	return (
		<>
			<Panel header={__('Initial tab', 'kudos-donations')}>
				<TextControl
					name="initial_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="initial_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel header={__('Subscription tab', 'kudos-donations')}>
				<TextControl
					name="subscription_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="subscription_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel header={__('Address tab', 'kudos-donations')}>
				<TextControl
					name="address_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="address_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel header={__('Message tab', 'kudos-donations')}>
				<TextControl
					name="message_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="message_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel header={__('Payment tab', 'kudos-donations')}>
				<TextControl
					name="payment_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="payment_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
		</>
	);
};
