import {TextInput} from "../FormElements/TextInput";
import {Toggle} from "../FormElements/Toggle"

const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    Fragment
} = wp.element;

const EmailReceiptsPanel = props => {

    return (
        <PanelBody
            title={__('Email Receipts', 'kudos-donations')}
            initialOpen={false}
        >

            <Toggle
                id='_kudos_email_receipt_enable'
                label={__('Send email receipts', 'kudos-donations')}
                help={__('Once a payment has been completed, you can automatically send an email receipt to the donor.', 'kudos-donations')}
                value={props.settings._kudos_email_receipt_enable}
                onChange={props.handleInputChange}
            />

            {props.settings._kudos_email_receipt_enable ? [

                <Fragment key="_kudos_email_bcc">
                    <TextInput
                        id='_kudos_email_bcc'
                        label={__("Send receipt copy to:", 'kudos-donations')}
                        value={props.settings._kudos_email_bcc}
                        disabled={props.isSaving}
                        onChange={props.handleInputChange}
                    />
                </Fragment>

            ]:''}

        </PanelBody>
    )
}

export {EmailReceiptsPanel}