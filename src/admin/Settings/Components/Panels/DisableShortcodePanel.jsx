import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {ToggleControl} from "@wordpress/components"

const DisableShortcodePanel = (props) => {

    return (
        <SettingCard title={__('Shortcode', 'kudos-donations')}>

            <ToggleControl
                label={__('Enable shortcode', 'kudos-donations')}
                help={__(
                    'This enables the shortcode integration. If you do not use the shortcode and only the block it is best to disable this.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_enable_shortcode || ''}
                onChange={() => props.handleInputChange("_kudos_enable_shortcode", !props.settings._kudos_enable_shortcode)}
            />

        </SettingCard>
    )
}

export {DisableShortcodePanel}
