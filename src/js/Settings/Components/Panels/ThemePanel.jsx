import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const { BaseControl, ColorPalette } = wp.components

const ThemePanel = (props) => {

    const colors = [
        {name: 'orange', color: '#ff9f1c'},
        {name: 'green', color: '#2ec4b6'},
    ]

    return (

        <SettingCard title={"Theme colour"} id="themeColor" {...props}>
            <BaseControl
                help={__('Set the colour for the Kudos button and the pop-up modal.', 'kudos-donations')}
            >
                <ColorPalette
                    id="_kudos_color_primary"
                    colors={colors}
                    value={props.settings._kudos_theme_colors.primary}
                    onChange={(value) => props.handleInputChange('_kudos_theme_colors', {primary: value})}
                    disableCustomColors={true}
                    clearable={false}
                />
            </BaseControl>
        </SettingCard>
    )
}

export {ThemePanel}
