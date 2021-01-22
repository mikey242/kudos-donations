const {__} = wp.i18n
const {PanelBody, TextControl, ToggleControl} = wp.components

const EmailReceiptsPanel = (props) => {
    return (
        <PanelBody
            title={__('Email receipts', 'kudos-donations')}
            initialOpen={true}
        >

            <ToggleControl
                label={__('Send email receipts', 'kudos-donations')}
                help={__(
                    'Once a payment has been completed, you can automatically send an email receipt to the donor.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_email_receipt_enable || ''}
                onChange={() => props.handleInputChange("_kudos_email_receipt_enable", !props.settings._kudos_email_receipt_enable)}
            />

            {props.settings._kudos_email_receipt_enable ?

                <TextControl
                    label={__('Send receipt copy to:', 'kudos-donations')}
                    help={__('Leave blank to disable.', 'kudos-donations')}
                    type={'text'}
                    value={props.settings._kudos_email_bcc || ''}
                    placeholder={props.placeholder}
                    disabled={props.isSaving}
                    onChange={(value) => props.handleInputChange("_kudos_email_bcc", value)}
                />

                : ''}
        </PanelBody>
    )
}

export {EmailReceiptsPanel}
