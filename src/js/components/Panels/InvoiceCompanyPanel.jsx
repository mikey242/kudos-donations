import {TextInput} from "../FormElements/TextInput"
import {TextArea} from "../FormElements/TextArea"
import {PrimaryButton} from "../FormElements/PrimaryButton"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    useState
} = wp.element;

const InvoiceCompanyPanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (option, value) => {
        setIsEdited(true)
        props.handleInputChange(option, value);
    }

    return (
        <PanelBody
            title={__('Company Details', 'kudos-donations')}
            initialOpen={false}
        >

            <TextInput
                id='_kudos_invoice_company_name'
                label="Name"
                value={props._kudos_invoice_company_name}
                disabled={props.isSaving}
                onChange={handleChange}
            />

            <TextArea
                id='_kudos_invoice_company_address'
                label="Address"
                value={props._kudos_invoice_company_address}
                disabled={props.isSaving}
                onChange={handleChange}
            />

            <PrimaryButton
                label="Save"
                disabled={!isEdited || props.isSaving}
                isBusy={props.isSaving}
                onClick={
                    ()=> {
                        props.updateSetting('_kudos_invoice_company_name', props._kudos_invoice_company_name)
                        props.updateSetting('_kudos_invoice_company_address', props._kudos_invoice_company_address)
                        setIsEdited(false)
                    }
                }
            />

        </PanelBody>
    )
}

export {InvoiceCompanyPanel}