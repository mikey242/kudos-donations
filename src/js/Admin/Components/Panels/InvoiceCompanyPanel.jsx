import {TextInput} from "../FormElements/TextInput"
import {TextArea} from "../FormElements/TextArea"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const InvoiceCompanyPanel = props => {

    return (
        <PanelBody
            title={__('Company Details', 'kudos-donations')}
            initialOpen={false}
        >

            <TextInput
                id='_kudos_invoice_company_name'
                label="Name"
                value={props.settings._kudos_invoice_company_name}
                disabled={props.isSaving}
                onChange={props.handleInputChange}
            />

            <TextArea
                id='_kudos_invoice_company_address'
                label="Address"
                value={props.settings._kudos_invoice_company_address}
                disabled={props.isSaving}
                onChange={props.handleInputChange}
            />

            <TextInput
                id='_kudos_invoice_vat_number'
                label="VAT Number"
                value={props.settings._kudos_invoice_vat_number}
                disabled={props.isSaving}
                onChange={props.handleInputChange}
            />

        </PanelBody>
    )
}

export {InvoiceCompanyPanel}