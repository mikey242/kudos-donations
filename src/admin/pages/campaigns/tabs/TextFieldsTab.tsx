import { __ } from '@wordpress/i18n';
import { TextAreaControl, TextControl } from '../../../controls';
import type { ReactNode } from 'react';
import { Panel } from '../../../components';
import { PanelList } from '../../AdminTabPanel';

const InitialTabPanel = () => (
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
);

const SubscriptionTabPanel = () => (
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
);

const AddressTabPanel = () => (
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
);

const MessageTabPanel = () => (
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
);

const PaymentTabPanel = () => (
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
);

export const TextFieldsTab = (): ReactNode => (
	<PanelList
		namespace="kudosCampaignPanels"
		tabName="text-fields"
		defaultPanels={[
			{ name: 'initial-tab', content: <InitialTabPanel /> },
			{ name: 'subscription-tab', content: <SubscriptionTabPanel /> },
			{ name: 'address-tab', content: <AddressTabPanel /> },
			{ name: 'message-tab', content: <MessageTabPanel /> },
			{ name: 'payment-tab', content: <PaymentTabPanel /> },
		]}
	/>
);
