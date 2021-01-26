import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const {Button, ToggleControl} = wp.components

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
                onChange={() => props.updateSetting("_kudos_debug_mode", !props.settings._kudos_debug_mode, true)}
            />

            {props.settings._kudos_debug_mode ?
                <Button
                    isLink
                    href={'admin.php?page=kudos-debug'}
                >
                    {__('Visit the debug page', 'kudos-donations')}
                </Button>
            : ''}

        </SettingCard>
    )
}

export {DebugModePanel}
