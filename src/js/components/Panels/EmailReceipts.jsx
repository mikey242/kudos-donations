const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

import {Toggle} from "../FormElements/Toggle"

const EmailReceipts = props => {

    return (
        <PanelBody
            title={__('Email Receipts')}
            initialOpen={false}
        >

            <Toggle
                id='_kudos_email_receipt_enable'
                label={'Send email receipts'}
                help={'Once a payment has been completed, you can automatically send an email receipt to the donor.'}
                value={props._kudos_email_receipt_enable}
                onChange={props.updateSetting}
            />

        </PanelBody>
    )
}

export {EmailReceipts}