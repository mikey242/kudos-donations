import {SettingCard} from "../SettingCard"

const ThemePanel = (props) => {

    const {__} = wp.i18n
    const {BaseControl, ColorPalette, ColorIndicator} = wp.components

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
                label={
                    <div className={"kd-mb-5 kd-flex"}>
                        {__('Selected color', 'kudos-donations') + ':'}
                        <ColorIndicator colorValue={props.settings._kudos_theme_colors.primary}/>
                    </div>
                }
                help={__('Set the colour for the Kudos button and the pop-up modal.', 'kudos-donations')}
            >
                <ColorPalette
                    id="_kudos_color_primary"
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
