import {TextInput} from "../FormElements/TextInput"
import {Checkbox} from "../FormElements/Checkbox"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const DonationFormPanel = props => {

    return (
        <PanelBody
            title={__('Donation Form', 'kudos-donations')}
            initialOpen={false}
        >

            <Checkbox
                id='_kudos_email_required'
                heading='Email'
                label='Required'
                value={props.settings._kudos_email_required}
                onChange={props.handleInputChange}
            />

            <Checkbox
                id='_kudos_name_required'
                heading='Name'
                label='Required'
                value={props.settings._kudos_name_required}
                onChange={props.handleInputChange}
            />

            <TextInput
                id='_kudos_form_header'
                label="Payment form header"
                value={props.settings._kudos_form_header}
                disabled={props.isSaving}
                onChange={props.handleInputChange}
            />

            <TextInput
                id='_kudos_form_text'
                label="Payment form text"
                value={props.settings._kudos_form_text}
                disabled={props.isSaving}
                onChange={props.handleInputChange}
            />

        </PanelBody>
    )
}

export {DonationFormPanel}