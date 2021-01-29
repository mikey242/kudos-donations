import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const { TextControl } = wp.components

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