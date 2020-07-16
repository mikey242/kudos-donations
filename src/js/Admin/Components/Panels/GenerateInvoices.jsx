import {Toggle} from "../FormElements/Toggle"
import {TextInput} from "../FormElements/TextInput"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    Fragment,
} = wp.element;

const GenerateInvoicesPanel = props => {

    return (
        <PanelBody
            title={__('Generate Invoices', 'kudos-donations')}
            initialOpen={false}
        >

            <Toggle
                id='_kudos_invoice_enable'
                label={'Generate invoices'}
                help={'Disable this if your server has issues with PDF generation.'}
                value={props.settings._kudos_invoice_enable}
                onChange={props.handleInputChange}
            />

            {props.settings._kudos_invoice_enable ? [

                <Fragment key="_kudos_attach_invoice">

                    <Toggle
                        id='_kudos_attach_invoice'
                        label={'Attach to emails'}
                        help={'When enabled, invoices will be attached to receipts emailed to donors.'}
                        value={props.settings._kudos_attach_invoice}
                        onChange={props.handleInputChange}
                    />

                </Fragment>

            ]:''}

        </PanelBody>
    )
}

export {GenerateInvoicesPanel}