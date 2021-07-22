import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl, ToggleControl} from "@wordpress/components"
import {Fragment} from "@wordpress/element"

const SpamProtectionPanel = (props) => {

    return (
        <SettingCard title={__('Spam protection', 'kudos-donations')}>
            <ToggleControl
                label={__('Enable spam protection', 'kudos-donations')}
                help={__(
                    'This enabled the built in spam protection. This includes a honeypot field and a timer that prevents very fast submission.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_spam_protection || ''}
                onChange={() => props.handleInputChange("_kudos_spam_protection", !props.settings._kudos_spam_protection)}
            />

        </SettingCard>
    )
}

export {SpamProtectionPanel}
