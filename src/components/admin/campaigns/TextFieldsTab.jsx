import { Fragment } from '@wordpress/element';
import { Panel } from '../../common/Panel';
import { __ } from '@wordpress/i18n';
import { TextAreaControl, TextControl } from '../../common/controls';
import React from 'react';

export const TextFieldsTab = () => {
	return (
		<Fragment>
			<Panel title={__('Initial tab', 'kudos-donations')}>
				<TextControl
					name="meta.initial_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="meta.initial_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel title={__('Subscription tab', 'kudos-donations')}>
				<TextControl
					name="meta.subscription_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="meta.subscription_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel title={__('Address tab', 'kudos-donations')}>
				<TextControl
					name="meta.address_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="meta.address_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel title={__('Message tab', 'kudos-donations')}>
				<TextControl
					name="meta.message_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="meta.message_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
			<Panel title={__('Payment tab', 'kudos-donations')}>
				<TextControl
					name="meta.payment_title"
					label={__('Title', 'kudos-donations')}
				/>
				<TextAreaControl
					name="meta.payment_description"
					label={__('Text', 'kudos-donations')}
				/>
			</Panel>
		</Fragment>
	);
};
