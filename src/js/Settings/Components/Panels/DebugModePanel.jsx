import {SettingCard} from "../SettingCard"

const DebugModePanel = (props) => {

    const {__} = wp.i18n
    const {ToggleControl} = wp.components

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
