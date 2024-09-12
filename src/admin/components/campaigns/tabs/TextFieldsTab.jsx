import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextAreaControl, TextControl } from '../../controls';
import React from 'react';
import { Panel, PanelBody } from '@wordpress/components';

export const TextFieldsTab = () => {
	return (
		<Fragment>
			<Panel header={__('Initial tab', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="meta.initial_title"
						label={__('Title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.initial_description"
						label={__('Text', 'kudos-donations')}
					/>
				</PanelBody>
			</Panel>
			<Panel header={__('Subscription tab', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="meta.subscription_title"
						label={__('Title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.subscription_description"
						label={__('Text', 'kudos-donations')}
					/>
				</PanelBody>
			</Panel>
			<Panel header={__('Address tab', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="meta.address_title"
						label={__('Title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.address_description"
						label={__('Text', 'kudos-donations')}
					/>
				</PanelBody>
			</Panel>
			<Panel header={__('Message tab', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="meta.message_title"
						label={__('Title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.message_description"
						label={__('Text', 'kudos-donations')}
					/>
				</PanelBody>
			</Panel>
			<Panel header={__('Payment tab', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="meta.payment_title"
						label={__('Title', 'kudos-donations')}
					/>
					<TextAreaControl
						name="meta.payment_description"
						label={__('Text', 'kudos-donations')}
					/>
				</PanelBody>
			</Panel>
		</Fragment>
	);
};
