import {SettingCard} from "../SettingCard"

const RenderModalFooter = (props) => {

    const {__} = wp.i18n
    const {ToggleControl} = wp.components

    return (
        <SettingCard title={__('Modal in footer', 'kudos-donations')}>

            <ToggleControl
                label={__('Render donate modal in footer', 'kudos-donations')}
                help={__(
                    'When enabled the donation modal HTML will be placed in the footer instead of in the content.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_modal_in_footer || ''}
                onChange={() => props.handleInputChange("_kudos_modal_in_footer", !props.settings._kudos_modal_in_footer)}
            />

        </SettingCard>
    )
}

export {RenderModalFooter}
