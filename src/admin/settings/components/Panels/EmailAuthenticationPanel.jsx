import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl} from "@wordpress/components"

const EmailAuthenticationPanel = (props) => {

    return (
        <SettingCard title={__('Authentication', 'kudos-donations')}>
            <TextControl
                label={__('Username', 'kudos-donations')}
                help={__(
                    'This is usually an email address.',
                    'kudos-donations'
                )}
                type={'text'}
                value={props.settings._kudos_smtp_username || ''}
                placeholder="user@domain.com"
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange("_kudos_smtp_username", value)}
            />
            <TextControl
                label={__('Password', 'kudos-donations')}
                help={__(
                    'This password will be stored as plain text in the database.',
                    'kudos-donations'
                )}
                type="password"
                value={props.settings._kudos_smtp_password || ''}
                placeholder="*****"
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange("_kudos_smtp_password", value)}
            />
        </SettingCard>
    )
}

export {EmailAuthenticationPanel}