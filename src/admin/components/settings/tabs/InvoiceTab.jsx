import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Panel, PanelBody } from '@wordpress/components';
import { TextAreaControl, TextControl } from '../../controls';

const InvoiceTab = () => {
	return (
		<Fragment>
			<Panel header={__('Your details', 'kudos-donations')}>
				<PanelBody>
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
				</PanelBody>
			</Panel>
			<Panel header={__('Other', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						name="_kudos_invoice_number"
						type="number"
						label={__('Current Invoice Number', 'kudos-donations')}
						help={__(
							'This number automatically increases as transactions are paid.',
							'kudos-donations'
						)}
					/>
				</PanelBody>
			</Panel>
		</Fragment>
	);
};

export { InvoiceTab };
