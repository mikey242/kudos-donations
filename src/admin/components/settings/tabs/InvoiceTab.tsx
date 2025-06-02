import { __ } from '@wordpress/i18n';
import React from 'react';
import { TextAreaControl, TextControl } from '../../controls';
import { Panel } from '../../Panel';

const InvoiceTab = (): React.ReactNode => {
	return (
		<>
			<Panel header={__('Your details', 'kudos-donations')}>
				<TextAreaControl
					name="_kudos_invoice_company_address"
					label={__('Invoice Address', 'kudos-donations')}
					help={__(
						'This is your address as it will appear on the invoice.',
						'kudos-donations'
					)}
				/>
				<TextControl
					name="_kudos_invoice_vat_number"
					label={__('VAT Number', 'kudos-donations')}
					help={__(
						'This will appear on your invoices.',
						'kudos-donations'
					)}
				/>
			</Panel>
			<Panel header={__('Other', 'kudos-donations')}>
				<TextControl
					name="_kudos_invoice_number"
					type="number"
					label={__('Current Invoice Number', 'kudos-donations')}
					help={__(
						'This will be the number assigned to the next successful payment. This will automatically increases as transactions are paid.',
						'kudos-donations'
					)}
				/>
			</Panel>
		</>
	);
};

export { InvoiceTab };
