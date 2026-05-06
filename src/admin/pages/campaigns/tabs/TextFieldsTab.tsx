import { __ } from '@wordpress/i18n';
import { TextAreaControl, TextControl } from '../../../controls';
import { Panel } from '../../../components';

export const InitialTextPanel = () => (
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

export const SubscriptionTextPanel = () => (
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

export const AddressTextPanel = () => (
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

export const MessageTextPanel = () => (
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

export const PaymentTextPanel = () => (
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
