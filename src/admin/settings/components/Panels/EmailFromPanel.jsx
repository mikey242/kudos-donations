import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl} from "@wordpress/components"

const EmailFromPanel = (props) => {

    return (
        <SettingCard title={__('From address', 'kudos-donations')}>
            <TextControl
                help={__(
                    'The email address emails will appear to be sent from. Leave empty to use same as username.',
                    'kudos-donations'
                )}
                type={'text'}
                value={props.settings._kudos_smtp_from || ''}
                placeholder="user@domain.com"
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange("_kudos_smtp_from", value)}
            />
        </SettingCard>
    )
}

export { EmailFromPanel }