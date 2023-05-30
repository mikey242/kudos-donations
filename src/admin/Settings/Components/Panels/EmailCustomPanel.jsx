import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {ToggleControl} from "@wordpress/components"

const EmailCustomPanel = (props) => {

    return (
        <SettingCard title={__('SMTP settings', 'kudos-donations')} initialOpen={false}>

            <ToggleControl
                label={__('Use custom email settings', 'kudos-donations')}
                help={__('Enable this to use your own SMTP server settings.', 'kudos-donations')}
                checked={props.settings._kudos_smtp_enable || ''}
                onChange={() => props.handleInputChange("_kudos_smtp_enable", !props.settings._kudos_smtp_enable)}
            />

        </SettingCard>
    )
}

export {EmailCustomPanel}