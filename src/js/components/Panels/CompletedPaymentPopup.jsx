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

const CompletedPaymentPopup = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (option, value) => {
        setIsEdited(true)
        props.handleInputChange(option, value);
    }

    return (
        <PanelBody
            title={__('Completed Payment Pop-up', 'kudos-donations')}
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

                    <PrimaryButton
                        label="Save"
                        disabled={!isEdited || props.isSaving}
                        isBusy={props.isSaving}
                        onClick={()=> {
                            props.updateSetting('_kudos_return_message_header', props._kudos_return_message_header)
                            props.updateSetting('_kudos_return_message_text', props._kudos_return_message_text)
                            setIsEdited(false)
                        }}
                    />

                </Fragment>

            ]:''}

        </PanelBody>
    )
}

export {CompletedPaymentPopup}