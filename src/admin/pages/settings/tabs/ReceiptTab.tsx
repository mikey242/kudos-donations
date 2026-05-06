import { __ } from '@wordpress/i18n';
import type { AdminTab } from '../../AdminTabPanel';
import React from 'react';
import { TextAreaControl, TextControl } from '../../../controls';
import { Panel } from '../../../components';

const YourDetailsPanel = () => (
	<Panel header={__('Your details', 'kudos-donations')}>
		<TextAreaControl
			name="_kudos_invoice_company_address"
			label={__('Receipt Address', 'kudos-donations')}
			help={__(
				'This is your address as it will appear on the receipt.',
				'kudos-donations'
			)}
		/>
		<TextControl
			name="_kudos_invoice_vat_number"
			label={__('VAT Number', 'kudos-donations')}
			help={__('This will appear on your receipt.', 'kudos-donations')}
		/>
	</Panel>
);

const OtherPanel = () => (
	<Panel header={__('Other', 'kudos-donations')}>
		<TextControl
			name="_kudos_invoice_number"
			type="number"
			label={__('Current Receipt Number', 'kudos-donations')}
			help={__(
				'This will be the number assigned to the next successful payment. This will automatically increases as transactions are paid.',
				'kudos-donations'
			)}
		/>
	</Panel>
);

export const ReceiptTab: AdminTab = {
	name: 'receipt',
	title: __('Receipt', 'kudos-donations'),
	panels: [
		{ name: 'your-details', content: <YourDetailsPanel /> },
		{ name: 'other', content: <OtherPanel /> },
	],
};
