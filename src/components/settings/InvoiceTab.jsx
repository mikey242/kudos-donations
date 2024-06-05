import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { TextAreaControl, TextControl } from '../controls';
import SettingsPanel from '../admin/SettingsPanel';

const InvoiceTab = () => {
	return (
		<Fragment>
			<SettingsPanel>
				<h2>{__('Your details', 'kudos-donations')}</h2>
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
			</SettingsPanel>
			<SettingsPanel>
				<TextControl
					name="_kudos_invoice_number"
					type="number"
					label={__('Current Invoice Number', 'kudos-donations')}
					help={__(
						'This number automatically increases as transactions are paid.',
						'kudos-donations'
					)}
				/>
			</SettingsPanel>
		</Fragment>
	);
};

export { InvoiceTab };
