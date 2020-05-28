import {Toggle} from "../FormElements/Toggle"
import {TextInput} from "../FormElements/TextInput"
import {PrimaryButton} from "../FormElements/PrimaryButton"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    Fragment
} = wp.element;

const CompletedPaymentPopup = props => {

    return (
        <PanelBody
            title={__('Completed Payment Pop-up', 'kudos-donations')}
            initialOpen={false}
        >

            <Toggle
                id='_kudos_return_message_enable'
                label={'Show pop-up message when payment complete'}
                help={'Enable this to show a pop-up thanking the customer for their donation.'}
                value={props.settings._kudos_return_message_enable}
                onChange={props.handleInputChange}
            />

            {props.settings._kudos_return_message_enable ? [

                <Fragment key="_kudos_return_message_fields">
                    <TextInput
                        id='_kudos_return_message_header'
                        label="Message header"
                        value={props.settings._kudos_return_message_header}
                        disabled={props.isSaving}
                        onChange={props.handleInputChange}
                    />
                    <TextInput
                        id='_kudos_return_message_text'
                        label="Message text"
                        value={props.settings._kudos_return_message_text}
                        placeHolder='Button label'
                        disabled={props.isSaving}
                        onChange={props.handleInputChange}
                    />

                </Fragment>

            ]:''}

        </PanelBody>
    )
}

export {CompletedPaymentPopup}