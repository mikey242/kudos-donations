import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {ToggleControl} from "@wordpress/components"

const RenderModalFooter = (props) => {

    return (
        <SettingCard title={__('Modal in footer', 'kudos-donations')}>

            <ToggleControl
                label={__('Render donate modal in footer', 'kudos-donations')}
                help={__(
                    'When enabled the donation modal HTML will be placed in the footer instead of in the content.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_donate_modal_in_footer || ''}
                onChange={() => props.handleInputChange("_kudos_donate_modal_in_footer", !props.settings._kudos_donate_modal_in_footer)}
            />

        </SettingCard>
    )
}

export {RenderModalFooter}