import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {ToggleControl} from "@wordpress/components"

const DebugModePanel = (props) => {

    return (
        <SettingCard title={__('Debug mode', 'kudos-donations')}>

            <ToggleControl
                label={__('Enable debug mode', 'kudos-donations')}
                help={__(
                    'This will enable the debug logging and a debug menu found under Kudos.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_debug_mode || ''}
                onChange={() => props.handleInputChange("_kudos_debug_mode", !props.settings._kudos_debug_mode)}
            />

        </SettingCard>
    )
}

export {DebugModePanel}
