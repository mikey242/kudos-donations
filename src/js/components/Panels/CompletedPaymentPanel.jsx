import {Toggle} from "../FormElements/Toggle"
import {TextInput} from "../FormElements/TextInput"
import {PrimaryButton} from "../FormElements/PrimaryButton"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    Fragment,
    useState
} = wp.element;

const CompletedPaymentPanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (e) => {
        setIsEdited(true)
        props.handleInputChange(e);
    }

    return (
        <PanelBody
            title={__('Completed Payment', 'kudos-donations')}
            initialOpen={false}
        >

            <Toggle
                id='_kudos_return_message_enable'
                label={'Show pop-up message when payment complete'}
                help={'Enable this to show a pop-up thanking the customer for their donation.'}
                value={props._kudos_return_message_enable}
                onChange={props.updateSetting}
            />

            {props._kudos_return_message_enable ? [

                <Fragment key="_kudos_return_message_fields">
                    <TextInput
                        id='_kudos_return_message_header'
                        label="Message header"
                        value={props._kudos_return_message_header}
                        disabled={props.isSaving}
                        onChange={handleChange}
                    />
                    <TextInput
                        id='_kudos_return_message_text'
                        label="Message text"
                        value={props._kudos_return_message_text}
                        placeHolder='Button label'
                        disabled={props.isSaving}
                        onChange={handleChange}
                    />
                </Fragment>

            ]:''}

            <Toggle
                id='_kudos_custom_return_enable'
                label={'Use custom return URL'}
                help={'After payment the customer is returned to the page where they clicked on the donation button. To use a different return URL, enable this option.'}
                value={props._kudos_custom_return_enable}
                onChange={props.updateSetting}
            />

            {props._kudos_custom_return_enable ? [

                <Fragment key="_kudos_custom_return_fields">

                    <TextInput
                        id='_kudos_custom_return_url'
                        label="URL"
                        help={'e.g https://mywebsite.com/thanks.'}
                        value={props._kudos_custom_return_url}
                        disabled={props.isSaving}
                        onChange={handleChange}
                    />

                </Fragment>

            ]:''}

            <PrimaryButton
                label="Save"
                disabled={!isEdited || props.isSaving}
                isBusy={props.isSaving}
                onClick={()=>props.updateSetting('_kudos_button_label', props._kudos_button_label)}
            />

        </PanelBody>
    )
}

export {CompletedPaymentPanel}