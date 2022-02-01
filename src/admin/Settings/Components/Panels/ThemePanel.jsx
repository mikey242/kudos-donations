import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {BaseControl, ColorPalette} from "@wordpress/components"


const ThemePanel = (props) => {

    const colors = [
        {name: 'orange', color: '#ff9f1c'},
        {name: 'green', color: '#2ec4b6'},
        {name: 'red', color: '#FF595E'},
        {name: 'blue', color: '#1982C4'},
        {name: 'pink', color: '#F46197'}
    ]

    return (

        <SettingCard title={__("Theme colour", 'kudos-donations')} id="themeColor" {...props}>
            <BaseControl
                help={__('Set the colour for the Kudos button and the pop-up modal.', 'kudos-donations')}
            >
                <ColorPalette
                    id="_kudos_color_primary"
                    className={"kd-flex-row kd-font-mono"}
                    colors={colors}
                    value={props.settings._kudos_theme_colors.primary}
                    onChange={(value) => props.handleInputChange('_kudos_theme_colors', {primary: value})}
                    disableCustomColors={false}
                    clearable={false}
                />
            </BaseControl>
        </SettingCard>
    )
}

export {ThemePanel}
