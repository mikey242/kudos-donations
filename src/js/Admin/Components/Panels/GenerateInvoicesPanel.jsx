import { Toggle } from '../FormElements/Toggle';
import axios from "axios"

const { __ } = wp.i18n;
const { PanelBody, Button, PanelRow } = wp.components;
const { Fragment, useState } = wp.element;

const GenerateInvoicesPanel = ( props ) => {

	const [ isBusy, setIsBusy ] = useState( false );

	const previewInvoice = () => {

		setIsBusy( true );

		// Perform get request
		axios
			.get(window.kudos.previewInvoiceUrl, {
				responseType: 'arraybuffer',
				headers: {
					// eslint-disable-next-line no-undef
					'X-WP-Nonce': wpApiSettings.nonce,
					'Accept': 'application/pdf'
				},
			})
			.then( ( response ) => {
				const url = window.URL.createObjectURL(
					new Blob([response.request.response], {
						type: 'application/pdf',
					})
				)
				const link = document.createElement('a');
				setIsBusy(false);
				link.href = url;
				link.setAttribute('download', 'sample.pdf');
				document.body.appendChild(link);
				link.click();
			} ).catch(function (error) {
				console.log(error);
			});
	};

	return (
		<PanelBody
			title={ __( 'Generate Invoices', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<Toggle
				id="_kudos_invoice_enable"
				label={ __( 'Generate invoices', 'kudos-donations' ) }
				help={ __(
					'Disable this if your server has issues with PDF generation.',
					'kudos-donations'
				) }
				value={ props.settings._kudos_invoice_enable }
				onChange={ props.handleInputChange }
			/>

			{ props.settings._kudos_invoice_enable
				? [
					<Fragment key="_kudos_attach_invoice">
						<Toggle
							id="_kudos_attach_invoice"
							label={ __(
								'Attach to emails',
								'kudos-donations'
							) }
							help={ __(
								'When enabled, invoices will be attached to receipts emailed to donors.',
								'kudos-donations'
							) }
							value={ props.settings._kudos_attach_invoice }
							onChange={ props.handleInputChange }
						/>
						<PanelRow>
							<Button
								disabled={isBusy}
								isLink
								onClick={ () => {
									previewInvoice();
								} }
							>
								{
									isBusy ?  __('Generating invoice', 'kudos-donations') : __('Preview invoice', 'kudos-donations')
								}
							</Button>
						</PanelRow>
					</Fragment>,
				  ]
				: '' }
		</PanelBody>
	);
};

export { GenerateInvoicesPanel };
