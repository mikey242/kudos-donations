import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl, ToggleControl} from "@wordpress/components"
import {Fragment} from "@wordpress/element"

const EmailReceiptsPanel = (props) => {

    return (
        <SettingCard title={__('Email receipts', 'kudos-donations')}>

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
                <Fragment>
                    <br/>
                    <ToggleControl
                        label={__('Show campaign name', 'kudos-donations')}
                        help={__(
                            'Show the campaign name in the receipt.',
                            'kudos-donations'
                        )}
                        checked={props.settings._kudos_email_show_campaign_name || ''}
                        onChange={() => props.handleInputChange("_kudos_email_show_campaign_name", !props.settings._kudos_email_show_campaign_name)}
                    />
                    <br/>
                    <TextControl
                        label={__('Send receipt copy to:', 'kudos-donations')}
                        help={__('Leave blank to disable.', 'kudos-donations')}
                        type={'text'}
                        value={props.settings._kudos_email_bcc || ''}
                        placeholder={props.placeholder}
                        disabled={props.isSaving}
                        onChange={(value) => props.handleInputChange("_kudos_email_bcc", value)}
                    />
                </Fragment>

                : ''}
        </SettingCard>
    )
}

export {EmailReceiptsPanel}
