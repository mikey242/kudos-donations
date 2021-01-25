import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const { BaseControl, ColorPalette } = wp.components

const ThemePanel = (props) => {

    const colors = [
        {name: 'orange', color: '#ff9f1c'},
        {name: 'green', color: '#2ec4b6'},
    ]

    return (

        <SettingCard title={"Theme Colour"}>
            <BaseControl
                id="_kudos_theme_color"
                label={__('Colour', 'kudos-donations')}
                help={__('Set the colour for the Kudos button and the pop-up modal.', 'kudos-donations')}
            >
                <ColorPalette
                    id="_kudos_theme_color"
                    colors={colors}
                    value={props.settings._kudos_theme_color}
                    onChange={(value) => props.handleInputChange('_kudos_theme_color', value)}
                    disableCustomColors={!props.settings._kudos_custom_theme_color}
                    clearable={false}
                />
            </BaseControl>
        </SettingCard>
    )
}

export {ThemePanel}
