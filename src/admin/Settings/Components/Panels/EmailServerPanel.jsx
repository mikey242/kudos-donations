import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl} from "@wordpress/components"

const EmailServerPanel = (props) => {

    return (
        <SettingCard title={__('Server', 'kudos-donations')}>
            <TextControl
                label={__('Host', 'kudos-donations')}
                help={__("Your email server's hostname", 'kudos-donations')}
                type={'text'}
                value={props.settings._kudos_smtp_host || ''}
                placeholder="mail.host.com"
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange("_kudos_smtp_host", value)}
            />
            <TextControl
                label={__('Port', 'kudos-donations')}
                help={__(
                    '587 (TLS), 465 (SSL), 25 (Unencrypted)',
                    'kudos-donations'
                )}
                type={'number'}
                value={props.settings._kudos_smtp_port || ''}
                placeholder="587"
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange("_kudos_smtp_port", value)}
            />
        </SettingCard>
    )
}

export {EmailServerPanel}