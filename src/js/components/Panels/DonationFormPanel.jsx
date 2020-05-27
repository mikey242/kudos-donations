import {TextInput} from "../FormElements/TextInput"
import {PrimaryButton} from "../FormElements/PrimaryButton"
import {Checkbox} from "../FormElements/Checkbox"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    useState
} = wp.element;

const DonationFormPanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (e) => {
        setIsEdited(true)
        props.handleInputChange(e);
    }

    return (
        <PanelBody
            title={__('Donation Form', 'kudos-donations')}
            initialOpen={false}
        >

            <Checkbox
                id='_kudos_email_required'
                heading='Email'
                label='Required'
                value={props._kudos_email_required}
                onChange={props.updateSetting}
            />

            <Checkbox
                id='_kudos_name_required'
                heading='Name'
                label='Required'
                value={props._kudos_name_required}
                onChange={props.updateSetting}
            />

            <TextInput
                id='_kudos_form_header'
                label="Payment form header"
                value={props._kudos_form_header}
                disabled={props.isSaving}
                onChange={handleChange}
            />

            <TextInput
                id='_kudos_form_text'
                label="Payment form text"
                value={props._kudos_form_text}
                disabled={props.isSaving}
                onChange={handleChange}
            />

            <PrimaryButton
                label= 'Save'
                disabled={!isEdited || props.isSaving}
                isBusy={props.isSaving}
                onClick={() => {
                    props.updateSetting('_kudos_form_header', props._kudos_form_header)
                    props.updateSetting('_kudos_form_text', props._kudos_form_text, false)
                }}
            />

        </PanelBody>
    )
}

export {DonationFormPanel}