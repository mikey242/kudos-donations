import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const {ToggleControl} = wp.components

const RenderModalFooter = (props) => {

    return (
        <SettingCard title={__('Modal in footer', 'kudos-donations')}>

            <ToggleControl
                label={__('Render donate modal in footer', 'kudos-donations')}
                help={__(
                    'This will place the donate form modal in the footer.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_donate_modal_in_footer || ''}
                onChange={() => props.handleInputChange("_kudos_donate_modal_in_footer", !props.settings._kudos_donate_modal_in_footer)}
            />

        </SettingCard>
    )
}

export {RenderModalFooter}
